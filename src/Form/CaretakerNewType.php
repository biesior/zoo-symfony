<?php

namespace App\Form;

use App\Entity\Caretaker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CaretakerNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('username')
            ->add('newPassword', RepeatedType::class, [
                'mapped'          => false,
                'required'        => true,
                'type'            => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'first_options'   => ['label' => 'New password'],
                'second_options'  => ['label' => 'Repeat Password'],

            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Caretaker::class,
        ]);
    }
}
