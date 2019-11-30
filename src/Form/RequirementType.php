<?php

namespace App\Form;

use App\Entity\Item;
use App\Entity\Requirement;
use App\Entity\Stats;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequirementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stats', EntityType::class, [
                'label' => 'Statistica',
                'class' => Stats::class,
                'choice_label' => 'label',
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Livello',
                'required' => true,
                'choices' => [
                    'o' => 1,
                    'oo' => 2,
                    'ooo' => 3,
                    'oooo' => 4,
                    'ooooo' => 5,
                ],
            ])
            ->add('clue', ClueType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Requirement::class,
            'csrf_protection' => false,
        ]);
    }

}