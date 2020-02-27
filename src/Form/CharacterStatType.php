<?php

namespace App\Form;

use App\Entity\Character;
use App\Entity\CharacterStat;
use App\Entity\Stats;
use Doctrine\ORM\EntityRepository;
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
                'label' => 'Meriti',
                'class' => Stats::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('m')
                        ->orderBy('m.label', 'ASC');
                },
                'group_by' => function ($el) {
                    if (false !== strpos($el->getLabel(), '0 - ')) {
                        return 'Difesa del potere';
                    }
                    if (false !== strpos($el->getLabel(), '1 - ')) {
                        return 'Disciplina';
                    }
                    return 'Merito';
                },
                'choice_label' => function ($el) {
                    if (false !== strpos($el->getLabel(), '0 - ')) {
                        return str_replace('0 - ', '', $el->getLabel());
                    }
                    if (false !== strpos($el->getLabel(), '1 - ')) {
                        return str_replace('1 - ', '', $el->getLabel());
                    }
                    return $el->getLabel();
                }
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Livello',
                'required' => true,
                'choices' => [
                    'Standard' => [
                        '1 - o' => 1,
                        '2 - oo' => 2,
                        '3 - ooo' => 3,
                        '4 - oooo' => 4,
                        '5 - ooooo' => 5,
                    ],
                    'Altri' => [
                        '6 - oooooo' => 6,
                        '7 - ooooooo' => 7,
                        '8 - oooooooo' => 8,
                        '9 - ooooooooo' => 9,
                        '10 - oooooooooo' => 10,
                    ]
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