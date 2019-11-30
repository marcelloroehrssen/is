<?php

namespace App\Form;

use App\Entity\Clue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('media', TextareaType::class, ['label' => 'Media'])
            ->add('type', ChoiceType::class, [
                'label' => 'Tipo',
                'required' => true,
                'choices' => [
                    'Immagine' => 'image',
                    'Video' => 'vid',
                    'Testo' => 'text',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Clue::class,
            'csrf_protection' => false,
        ]);
    }

}