<?php

namespace App\Form;

use App\Entity\Character;
use App\Entity\CharacterStat;
use App\Entity\Stats;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacterStatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stat', EntityType::class, [
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CharacterStat::class,
            'csrf_protection' => false,
        ]);
    }
}