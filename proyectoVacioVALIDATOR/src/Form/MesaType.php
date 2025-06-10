<?php

namespace App\Form;

use App\Entity\Mesa;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MesaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('identificador', TextType::class, [
                'label' => 'Identificador',
            ])
            ->add('capacidad', IntegerType::class, [
                'label' => 'Nº de Comensales',
            ])
            ->add('guardar', SubmitType::class, [
                'label' => 'Guardar',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);
    }
}
