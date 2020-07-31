<?php

namespace App\Controller;

use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AbstractApiController
 * @package App\Controller
 *
 * @Route("/api/v1")
 * @SWG\Tag(name="Authentication")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/login_check", name="login_check", methods={"POST"})
     * @throws \Exception
     * @SWG\Response(
     *     response=200,
     *     description="Return token",
     * )
     * @SWG\Parameter(
     *     name="login",
     *     in="body",
     *     @SWG\Schema(
     *         @SWG\Property(property="username", type="string"),
     *         @SWG\Property(property="password", type="string")
     *     )
     * )
     */
    public function index()
    {
        throw new \Exception("Should not be reached !");
    }
}
