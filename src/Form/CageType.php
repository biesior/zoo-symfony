<?php

namespace App\Form;

use App\Entity\Cage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('slug', null, [
                'required' => true,
                'label'    => 'Slug',
                'attr'     => ['placeholder' => 'Please enter unique slug, required']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cage::class,
        ]);
    }
}
