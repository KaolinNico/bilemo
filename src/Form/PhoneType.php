<?php

namespace App\Form;

use App\Entity\Phone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reference', TextType::class)
            ->add('brand', TextType::class)
            ->add('model', TextType::class)
            ->add('price', TextType::class)
            ->add('processor', TextType::class)
            ->add('screen', TextType::class)
            ->add('camera', TextType::class)
            ->add('ram', TextType::class)
            ->add('network', TextType::class)
            ->add('connectivity', TextType::class)
            ->add('system', TextType::class)
            ->add('autonomy', TextType::class)
            ->add('dimensions', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Phone::class,
        ]);
    }
}
