<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/users")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="users_list", methods={"GET"})
     * @param UserRepository $userRepository
     * @return Response
     */
    public function indexAction(UserRepository $userRepository) :Response
    {
        return $this->json(
            $userRepository->findByCustomer(5),
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
     */
    public function showAction(User $user) :Response
    {
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
     * @return Response
     */
    public function newAction(Request $request) :Response
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

        $customerRepository = $this->getDoctrine()->getRepository(Customer::class);
        $user->setCustomer($customerRepository->find(5));
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
     */
    public function editAction(Request $request, User $user) :Response
    {
        if ($user->getCustomer()->getId() !== 5) {
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
     */
    public function deleteAction(Request $request, User $user) :Response
    {
        if ($user->getCustomer()->getId() !== 5) {
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

    public function serializeErrors(Form $form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $formError) {
            $errors['globals'][] = $formError->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->subSerializeErrors($childForm)) {
                    $errors['fields'][$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }

    private function subSerializeErrors(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->serializeErrors($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }
}
