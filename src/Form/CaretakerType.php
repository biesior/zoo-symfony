<?php

namespace App\Form;

use App\Entity\Caretaker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CaretakerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('username')
            ->add('slug', null, [
                'required' => true,
                'label'    => 'Slug',
                'attr'     => ['placeholder' => 'Please enter unique slug, required']
            ])
            ->add('roles', ChoiceType::class, [
                'required' => true,
                'label'    => 'User roles',
                'multiple' => true,
                'expanded' => true,
                'choices'  => [
                    'User (always)' => Caretaker::ROLE_USER,
                    'Admin'         => Caretaker::ROLE_ADMIN,
                    'Visitor'       => Caretaker::ROLE_VISITOR,
                ]
            ])
            ->add('newPassword', RepeatedType::class, [
                'mapped'          => false,
                'required'        => false,
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
