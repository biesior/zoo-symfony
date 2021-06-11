<?php

namespace App\Form;

use App\Entity\Animal;
use App\Entity\Caretaker;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnimalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'required' => true,
                'label'    => 'Animal\'s name',
                'attr'     => ['placeholder' => 'Common name of the animal, required']
            ])
            ->add('slug', null, [
                'required' => true,
                'label'    => 'Slug',
                'attr'     => ['placeholder' => 'Please enter unique slug, required']
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['placeholder' => 'Add short description of the animal)']
            ])
            ->add('legs', IntegerType::class, [
                'attr' => ['placeholder' => 'Number of legs between 0 and 100 (aliens with thousands of legs are not allowed)']
            ])
            ->add('birthDate')
            ->add('canItFly', CheckboxType::class, [
                'required' => false,
            ])
            ->add('cage')
            ->add('caretakers', EntityType::class, [
                'class'        => Caretaker::class,
                'multiple'     => true,
                'expanded'     => true,
                'choice_label' => 'name',
                'help'         => 'Choose caretakers who cares about this animal',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Animal::class,
        ]);
    }
}
