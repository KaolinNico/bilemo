<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Exception\BadFormException;
use App\Exception\BadJsonException;
use App\Form\PhoneType;
use App\Repository\PhoneRepository;
use DateInterval;
use Nelmio\ApiDocBundle\Annotation\Security;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

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
     * @param CacheInterface $cache
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
     * @throws InvalidArgumentException
     */
    public function indexAction(PhoneRepository $phoneRepository, CacheInterface $cache): Response
    {
        return $cache->get('phones', function (ItemInterface $item) use ($phoneRepository) {
            $item->expiresAfter(DateInterval::createFromDateString("1 hour"));
            return $this->json(
                $phoneRepository->findAll(),
                200
            );
        });
    }

    /**
     * @Route("/{id}", name="phone_show", methods={"GET"})
     * @param Phone $phone
     * @param CacheInterface $cache
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
    public function showAction(Phone $phone, CacheInterface $cache): Response
    {
        $key = "phone_" . $phone->getId();
        return $cache->get($key, function (ItemInterface $item) use ($phone) {
            $item->expiresAfter(DateInterval::createFromDateString("1 hour"));
            return $this->json(
                $phone,
                200
            );
        });
    }

    /**
     * @Route("/", name="phone_new", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param CacheInterface $cache
     * @return Response
     *
     * @throws BadFormException
     * @throws BadJsonException
     * @throws InvalidArgumentException
     * @SWG\Response(
     *     response=201,
     *     description="Phone created (Administrator only)",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Phone::class))
     *     )
     * )
     */
    public function newAction(Request $request, CacheInterface $cache): Response
    {
        $phone = new Phone();
        $form = $this->createForm(PhoneType::class, $phone);

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            throw new BadJsonException();
        }

        $form->submit($data);

        if (!($form->isSubmitted() && $form->isValid())) {
            throw new BadFormException($form);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($phone);
        $entityManager->flush();

        $cache->delete("phones");

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
     * @param CacheInterface $cache
     * @return Response
     *
     * @throws BadFormException
     * @throws BadJsonException
     * @throws InvalidArgumentException
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
    public function editAction(Request $request, Phone $phone, CacheInterface $cache): Response
    {
        $form = $this->createForm(PhoneType::class, $phone);

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            throw new BadJsonException();
        }

        $form->submit($data);

        if (!($form->isSubmitted() && $form->isValid())) {
            throw new BadFormException($form);
        }

        $cache->delete("phones");
        $cache->delete("phone_" . $phone->getId());

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
     * @param CacheInterface $cache
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
     * @throws InvalidArgumentException
     */
    public function deleteAction(Request $request, Phone $phone, CacheInterface $cache): Response
    {
        $cache->delete("phones");
        $cache->delete("phone_" . $phone->getId());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($phone);
        $entityManager->flush();


        return $this->json(
            ['success' => true],
            200
        );
    }
}
