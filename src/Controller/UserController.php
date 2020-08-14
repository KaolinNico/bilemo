<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Exception\BadFormException;
use App\Exception\BadJsonException;
use App\Form\UserType;
use App\Repository\UserRepository;
use DateInterval;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Cache\InvalidArgumentException;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @Route("/api/v1/users")
 * @SWG\Tag(name="Users")
 * @Security(name="Bearer")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="users_list", methods={"GET"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @return JsonResponse
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
    public function indexAction(Request $request, UserRepository $userRepository, CacheInterface $cache, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $key = 'users_list_' . $page . '_' . $limit . $this->getUser()->getId();
        return $cache->get($key, function (ItemInterface $item) use ($userRepository, $serializer, $request, $page, $limit) {
            $item->expiresAfter(DateInterval::createFromDateString('1 hour'));
            $context = SerializationContext::create()->setGroups([
                'Default',
                'users_list'
            ]);

            $users = $userRepository->findByCustomer($this->getUser()->getId());
            $offset = ($page - 1) * $limit;
            $max_page = count($users) / $limit;

            $collection = new CollectionRepresentation(
                array_slice($users, $offset, $limit)
            );


            $pagination = new PaginatedRepresentation(
                $collection,
                "users_list",
                [],
                $page,
                $limit,
                $max_page,
                'page',
                'limit',
                true,
                count($users)
            );

            $data = $serializer->serialize(
                $pagination,
                'json',
                $context
            );

            return new JsonResponse($data, 200, [], true);
        });

    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     * @param User $user
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @return JsonResponse
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
    public function showAction(User $user, CacheInterface $cache, SerializerInterface $serializer): JsonResponse
    {
        $key = "user_" . $user->getId();
        return $cache->get($key, function (ItemInterface $item) use ($user, $serializer) {
            $item->expiresAfter(DateInterval::createFromDateString('1 hour'));

            if ($user->getCustomer()->getId() !== $this->getUser()->getId()) {
                return $this->json(['message' => '400 - Bad Request'], 400);
            }

            $context = SerializationContext::create()->setGroups(['user_show']);
            $data = $serializer->serialize(
                $user,
                'json',
                $context
            );

            return new JsonResponse($data, 200, [], true);
        });

    }

    /**
     * @Route("/", name="user_new", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @return JsonResponse
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
    public function newAction(Request $request, UserPasswordEncoderInterface $passwordEncoder, CacheInterface $cache, SerializerInterface $serializer): JsonResponse
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

        $user->setCustomer($this->getUser());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $cache->delete("users_list_" . $this->getUser()->getId());

        $context = SerializationContext::create()->setGroups(['user_show']);

        $data = $serializer->serialize(
            $user,
            'json',
            $context
        );
        return new JsonResponse($data, 201, [], true);
    }

    /**
     * @Route("/{id}", name="user_edit", methods={"PUT"})
     * @param Request $request
     * @param User $user
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @return JsonResponse
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
    public function editAction(Request $request, User $user, CacheInterface $cache, SerializerInterface $serializer): JsonResponse
    {
        if ($user->getCustomer()->getId() !== $this->getUser()->getId()) {
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
        $user->setCustomer($customerRepository->find($this->getUser()->getId()));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $cache->delete("users_list_" . $user->getCustomer()->getId());
        $cache->delete("user_" . $user->getId());

        $context = SerializationContext::create()->setGroups(['user_show']);
        $data = $serializer->serialize(
            $user,
            'json',
            $context
        );

        return new JsonResponse($data, 200, [], true);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     * @param Request $request
     * @param User $user
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @return JsonResponse
     *
     * @throws InvalidArgumentException
     * @SWG\Response(
     *     response=204,
     *     description="Delete an user",
     * ),
     * @SWG\Parameter(
     *     in="path",
     *     name="id",
     *     type="integer",
     *     description="user id"
     * )
     */
    public function deleteAction(Request $request, User $user, CacheInterface $cache, SerializerInterface $serializer): JsonResponse
    {
        if ($user->getCustomer()->getId() !== $this->getUser()->getId()) {
            return $this->json(['message' => '400 - Bad Request'], 400);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        $cache->delete("users_list_" . $user->getCustomer()->getId());
        $cache->delete("user_" . $user->getId());

        return new JsonResponse(null, 204);
    }
}
