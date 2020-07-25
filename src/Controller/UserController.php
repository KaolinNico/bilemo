<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Exception\BadFormException;
use App\Exception\BadJsonException;
use App\Form\UserType;
use App\Repository\UserRepository;
use DateInterval;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Cache\InvalidArgumentException;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @Route("/api/v1/users")
 * @SWG\Tag(name="Users")
 * @Security(name="Bearer")
 */
class UserController extends AbstractApiController
{
    /**
     * @Route("/", name="users_list", methods={"GET"})
     * @param UserRepository $userRepository
     * @param CacheInterface $cache
     * @return Response
     *
     * @throws InvalidArgumentException
     * @SWG\Response(
     *     response=200,
     *     description="Return list of all users for a customer",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"users_list"}))
     *     )
     * )
     */
    public function indexAction(UserRepository $userRepository, CacheInterface $cache): Response
    {
        $key = "users_list_" . $this->getUser()->getCustomer()->getId();
        return $cache->get($key, function (ItemInterface $item) use ($userRepository) {
            $item->expiresAfter(DateInterval::createFromDateString('1 hour'));
            return $this->json(
                $userRepository->findByCustomer($this->getUser()->getCustomer()->getId()),
                200,
                [],
                [
                    "groups" => [
                        "users_list"
                    ]
                ]
            );
        });

    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     * @param User $user
     * @param CacheInterface $cache
     * @return Response
     *
     * @throws InvalidArgumentException
     * @SWG\Response(
     *     response=200,
     *     description="Returns details for an user",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"user_show"}))
     *     )
     * ),
     * @SWG\Parameter(
     *     in="path",
     *     name="id",
     *     type="integer",
     *     description="user id"
     * )
     */
    public function showAction(User $user, CacheInterface $cache): Response
    {
        $key = "user_" . $user->getId();
        return $cache->get($key, function (ItemInterface $item) use ($user) {
            $item->expiresAfter(DateInterval::createFromDateString('1 hour'));

            if ($user->getCustomer()->getId() !== $this->getUser()->getCustomer()->getId()) {
                return $this->json(['message' => '400 - Bad Request'], 400);
            }

            return $this->json(
                $user,
                200,
                [],
                [
                    "groups" => [
                        "user_show"
                    ]
                ]
            );
        });

    }

    /**
     * @Route("/", name="user_new", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param CacheInterface $cache
     * @return Response
     *
     * @throws BadFormException
     * @throws BadJsonException
     * @throws InvalidArgumentException
     * @SWG\Response(
     *     response=201,
     *     description="Create new user",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"user_show"}))
     *     )
     * )
     */
    public function newAction(Request $request, UserPasswordEncoderInterface $passwordEncoder, CacheInterface $cache): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            throw new BadJsonException();
        }

        $form->submit($data);

        if (!($form->isSubmitted() && $form->isValid())) {
            throw new BadFormException($form);
        }

        $user->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()));
        $user->setCustomer($this->getUser()->getCustomer());
        $user->setRoles(['ROLE_USER']);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $cache->delete("users_list_" . $this->getUser()->getCustomer()->getId());

        return $this->json(
            $user,
            201,
            [],
            [
                "groups" => [
                    "user_show"
                ]
            ]
        );
    }

    /**
     * @Route("/{id}", name="user_edit", methods={"PUT"})
     * @param Request $request
     * @param User $user
     * @param CacheInterface $cache
     * @return Response
     *
     * @throws BadFormException
     * @throws BadJsonException
     * @throws InvalidArgumentException
     * @SWG\Response(
     *     response=200,
     *     description="Edit an user",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"user_show"}))
     *     )
     * ),
     * @SWG\Parameter(
     *     in="path",
     *     name="id",
     *     type="integer",
     *     description="user id"
     * )
     */
    public function editAction(Request $request, User $user, CacheInterface $cache): Response
    {
        if ($user->getCustomer()->getId() !== $this->getUser()->getCustomer()->getId()) {
            return $this->json(['message' => '400 - Bad Request'], 400);
        }

        $form = $this->createForm(UserType::class, $user);

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            throw new BadJsonException();
        }

        $form->submit($data);
        if (!($form->isSubmitted() && $form->isValid())) {
            throw new BadFormException($form);
        }

        $customerRepository = $this->getDoctrine()->getRepository(Customer::class);
        $user->setCustomer($customerRepository->find($this->getUser()->getCustomer()->getId()));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $cache->delete("users_list_" . $user->getCustomer()->getId());
        $cache->delete("user_" . $user->getId());

        return $this->json(
            $user,
            200,
            [],
            [
                "groups" => [
                    "user_show"
                ]
            ]
        );
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     * @param Request $request
     * @param User $user
     * @param CacheInterface $cache
     * @return Response
     *
     * @SWG\Response(
     *     response=200,
     *     description="Delete an user",
     *     @SWG\Schema(
     *         @SWG\Property(property="success", type="boolean", description="return success"),
     *     )
     * ),
     * @SWG\Parameter(
     *     in="path",
     *     name="id",
     *     type="integer",
     *     description="user id"
     * )
     * @throws InvalidArgumentException
     */
    public function deleteAction(Request $request, User $user, CacheInterface $cache): Response
    {
        if ($user->getCustomer()->getId() !== $this->getUser()->getCustomer()->getId()) {
            return $this->json(['message' => '400 - Bad Request'], 400);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        $cache->delete("users_list_" . $user->getCustomer()->getId());
        $cache->delete("user_" . $user->getId());

        return $this->json(
            ['success' => true],
            200
        );
    }
}
