<?php


namespace App\Normalizer;


use App\Exception\BadJsonException;
use App\Exception\BadNormalizerException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BadJsonNormalizer extends AbstractNormalizer
{

    public function getExceptionSupported(): string
    {
        return BadJsonException::class;
    }

    public function normalize(\Throwable $exception): Response
    {
        if (!$this->support($exception)) {
            throw new BadNormalizerException();
        }

        return new JsonResponse(['message' => 'Invalid Json'], 400);
    }
}