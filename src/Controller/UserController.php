<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
     * @return Response
     *
     * @SWG\Response(
     *     response=200,
     *     description="Return list of all users for a customer",
     * )
     */
    public function indexAction(UserRepository $userRepository) :Response
    {
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
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     * @param User $user
     * @return Response
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns details for an user",
     * )
     */
    public function showAction(User $user) :Response
    {
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
    }

    /**
     * @Route("/", name="user_new", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     *
     * @SWG\Response(
     *     response=201,
     *     description="Create new user",
     * )
     */
    public function newAction(Request $request, UserPasswordEncoderInterface $passwordEncoder) :Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'Invalid Json'], 400);
        }

        $form->submit($data);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->json(['message' => '400 - Bad Request'], 400);
        }

        $user->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()));
        $user->setCustomer($this->getUser()->getCustomer());
        $user->setRoles(['ROLE_USER']);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

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
     * @return Response
     *
     * @SWG\Response(
     *     response=200,
     *     description="Edit an user",
     * )
     */
    public function editAction(Request $request, User $user) :Response
    {
        if ($user->getCustomer()->getId() !== $this->getUser()->getCustomer()->getId()) {
            return $this->json(['message' => '400 - Bad Request'], 400);
        }

        $form = $this->createForm(UserType::class, $user);

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'Invalid Json'], 400);
        }

        $form->submit($data);
        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->json($this->serializeErrors($form), 400);
        }

        $customerRepository = $this->getDoctrine()->getRepository(Customer::class);
        $user->setCustomer($customerRepository->find(5));
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

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
     * @return Response
     *
     * @SWG\Response(
     *     response=200,
     *     description="Delete an user",
     * )
     */
    public function deleteAction(Request $request, User $user) :Response
    {
        if ($user->getCustomer()->getId() !== $this->getUser()->getCustomer()->getId()) {
            return $this->json(['message' => '400 - Bad Request'], 400);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(
            ['success' => true],
            200
        );
    }
}
