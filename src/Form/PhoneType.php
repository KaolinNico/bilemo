<?php

namespace App\Form;

use App\Entity\Phone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reference')
            ->add('brand')
            ->add('model')
            ->add('price')
            ->add('processor')
            ->add('screen')
            ->add('camera')
            ->add('ram')
            ->add('network')
            ->add('connectivity')
            ->add('system')
            ->add('autonomy')
            ->add('dimensions')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Phone::class,
        ]);
    }
}
