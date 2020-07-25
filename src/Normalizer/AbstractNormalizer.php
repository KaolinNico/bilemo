<?php


namespace App\Normalizer;


abstract class AbstractNormalizer implements NormalizerInterface
{
    public abstract function getExceptionSupported(): string;

    public function support(\Throwable $exception): bool
    {
        $exceptionSupported = $this->getExceptionSupported();

        return $exception instanceof $exceptionSupported;
    }
}