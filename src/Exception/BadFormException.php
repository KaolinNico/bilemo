<?php


namespace App\Exception;


use Symfony\Component\Form\FormInterface;
use Throwable;

class BadFormException extends \Exception
{
    /**
     * @var FormInterface
     */
    private $form;

    public function __construct(FormInterface $form, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->form = $form;
    }

    /**
     * @return FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }
}