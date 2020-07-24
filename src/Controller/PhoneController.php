<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Form\PhoneType;
use App\Repository\PhoneRepository;
use Nelmio\ApiDocBundle\Annotation\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/phones")
 * @SWG\Tag(name="Phones")
 * @Security(name="Bearer")
 */
class PhoneController extends AbstractApiController
{
    /**
     * @Route("/", name="phones_list", methods={"GET"})
     * @param PhoneRepository $phoneRepository
     * @return Response
     *
     * @SWG\Response(
     *     response=200,
     *     description="Return list of all phones",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Phone::class))
     *     )
     * )
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
     *
     * @SWG\Response(
     *     response=200,
     *     description="Return details for a phone",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Phone::class))
     *     )
     * ),
     * @SWG\Parameter(
     *     in="path",
     *     name="id",
     *     type="integer",
     *     description="phone id"
     * )
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
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @return Response
     *
     * @SWG\Response(
     *     response=201,
     *     description="Phone created (Administrator only)",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Phone::class))
     *     )
     * )
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
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param Phone $phone
     * @return Response
     *
     * @SWG\Response(
     *     response=200,
     *     description="Phone edited (Administrator only)",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Phone::class))
     *     )
     * ),
     * @SWG\Parameter(
     *     in="path",
     *     name="id",
     *     type="integer",
     *     description="phone id"
     * )
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
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param Phone $phone
     * @return Response
     *
     * @SWG\Response(
     *     response=200,
     *     description="Phone deleted (Administrator only)",
     *     @SWG\Schema(
     *         @SWG\Property(property="success", type="boolean", description="return success"),
     *     )
     * ),
     * @SWG\Parameter(
     *     in="path",
     *     name="id",
     *     type="integer",
     *     description="phone id"
     * )
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
}
