<?php

namespace App\Normalizer;

use Symfony\Component\HttpFoundation\Response;

interface NormalizerInterface
{
    public function normalize(\Throwable $exception): Response;

    public function support(\Throwable $exception): bool;
}