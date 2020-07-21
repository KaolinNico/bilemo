<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @Route("/{id}, name="phone_show", methods={"GET"})
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
}
