<?php


namespace App\Normalizer;


use App\Exception\BadFormException;
use App\Exception\BadNormalizerException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BadFormNormalizer extends AbstractNormalizer
{

    public function getExceptionSupported(): string
    {
        return BadFormException::class;
    }

    public function normalize(\Throwable $exception): Response
    {
        if (!$this->support($exception)) {
            throw new BadNormalizerException();
        }

        return new JsonResponse(['message' => 'Bad Request', 'errors' => $this->serializeErrors($exception->getForm())], 400);
    }

    protected function serializeErrors(FormInterface $form): array
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

    protected function subSerializeErrors(FormInterface $form): array
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