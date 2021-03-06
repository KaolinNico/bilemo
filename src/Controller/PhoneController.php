<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Exception\BadFormException;
use App\Exception\BadJsonException;
use App\Form\PhoneType;
use App\Repository\PhoneRepository;
use DateInterval;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @Route("/api/v1/phones")
 * @SWG\Tag(name="Phones")
 * @Security(name="Bearer")
 */
class PhoneController extends AbstractController
{
    /**
     * @Route("/", name="phones_list", methods={"GET"})
     * @param Request $request
     * @param PhoneRepository $phoneRepository
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @return JsonResponse
     *
     * @throws InvalidArgumentException
     * @SWG\Response(
     *     response=200,
     *     description="Return list of all phones",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Phone::class))
     *     )
     * )
     */
    public function indexAction(Request $request, PhoneRepository $phoneRepository, CacheInterface $cache, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        return $cache->get('phones_' . $page . '_' . $limit, function (ItemInterface $item) use ($phoneRepository, $serializer, $request, $page, $limit) {
            $item->expiresAfter(DateInterval::createFromDateString("1 hour"));

            $phones = $phoneRepository->findAll();
            $offset = ($page - 1) * $limit;
            $max_page = count($phones) / $limit;

            $collection = new CollectionRepresentation(
                array_slice($phones, $offset, $limit)
            );
            $pagination = new PaginatedRepresentation(
                $collection,
                "phones_list",
                [],
                $page,
                $limit,
                $max_page,
                'page',
                'limit',
                true,
                count($phones)
            );


            $data = $serializer->serialize(
                $pagination,
                'json'
            );

            return new JsonResponse($data, 200, [], true);
        });
    }

    /**
     * @Route("/{id}", name="phone_show", methods={"GET"})
     * @param Phone $phone
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @return JsonResponse
     *
     * @throws InvalidArgumentException
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
    public function showAction(Phone $phone, CacheInterface $cache, SerializerInterface $serializer): JsonResponse
    {
        $key = "phone_" . $phone->getId();
        return $cache->get($key, function (ItemInterface $item) use ($phone, $serializer) {
            $item->expiresAfter(DateInterval::createFromDateString("1 hour"));

            $data = $serializer->serialize(
                $phone,
                'json'
            );

            return new JsonResponse($data, 200, [], true);
        });
    }

    /**
     * @Route("/", name="phone_new", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @return JsonResponse
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
    public function newAction(Request $request, CacheInterface $cache, SerializerInterface $serializer): JsonResponse
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

        $data = $serializer->serialize(
            $phone,
            'json'
        );

        return new JsonResponse($data, 201, [], true);
    }

    /**
     * @Route("/{id}", name="phone_edit", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param Phone $phone
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @return JsonResponse
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
    public function editAction(Request $request, Phone $phone, CacheInterface $cache, SerializerInterface $serializer): JsonResponse
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

        $data = $serializer->serialize(
            $phone,
            'json'
        );

        return new JsonResponse($data, 200, [], true);
    }

    /**
     * @Route("/{id}", name="phone_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param Phone $phone
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @return JsonResponse
     *
     * @throws InvalidArgumentException
     * @SWG\Response(
     *     response=204,
     *     description="Phone deleted (Administrator only)",
     * ),
     * @SWG\Parameter(
     *     in="path",
     *     name="id",
     *     type="integer",
     *     description="phone id"
     * )
     */
    public function deleteAction(Request $request, Phone $phone, CacheInterface $cache, SerializerInterface $serializer): JsonResponse
    {
        $cache->delete("phones");
        $cache->delete("phone_" . $phone->getId());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($phone);
        $entityManager->flush();

        return new JsonResponse(null, 204);
    }
}
