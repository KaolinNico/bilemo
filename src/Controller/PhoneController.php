<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Form\PhoneType;
use App\Repository\PhoneRepository;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/phones")
 */
class PhoneController extends AbstractController
{
    /**
     * @Route("/", name="phones_list", methods={"GET"})
     * @param PhoneRepository $phoneRepository
     * @return Response
     */
    public function indexAction(PhoneRepository $phoneRepository) :Response
    {
        return $this->json(
            $phoneRepository->findAll(),
            200
        );
    }

    /**
     * @Route("/{id}", name="phone_show", methods={"GET"})
     * @param Phone $phone
     * @return Response
     */
    public function showAction(Phone $phone) :Response
    {
        return $this->json(
            $phone,
            200
        );
    }

    /**
     * @Route("/", name="phone_new", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request) :Response
    {
        $phone = new Phone();
        $form = $this->createForm(PhoneType::class, $phone);

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'Invalid Json'], 400);
        }

        $form->submit($data);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->json(['message' => '400 - Bad Request'], 400);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($phone);
        $entityManager->flush();

        return $this->json(
            $phone,
            201
        );
    }

    /**
     * @Route("/{id}", name="phone_edit", methods={"PUT"})
     * @param Request $request
     * @param Phone $phone
     * @return Response
     */
    public function editAction(Request $request, Phone $phone) :Response
    {
        $form = $this->createForm(PhoneType::class, $phone);

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'Invalid Json'], 400);
        }

        $form->submit($data);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->json($this->serializeErrors($form), 400);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($phone);
        $entityManager->flush();

        return $this->json(
            $phone,
            200
        );
    }

    /**
     * @Route("/{id}", name="phone_delete", methods={"DELETE"})
     * @param Request $request
     * @param Phone $phone
     * @return Response
     */
    public function deleteAction(Request $request, Phone $phone) :Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($phone);
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
