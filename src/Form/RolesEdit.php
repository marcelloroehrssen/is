<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 14/05/2018
 * Time: 14:45
 */

namespace App\Form;


use App\Entity\Character;
use App\Entity\Clan;
use App\Entity\Covenant;
use App\Entity\Figs;
use App\Entity\Rank;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RolesEdit extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => false,
                'choices'  => array(
                    'PG' => 'PG',
                    'PNG' => 'PNG',
                ),
            ])
            ->add('clan', EntityType::class, [
                'label' => false,
                'class' => Clan::class,
                'choice_label' => 'name'
            ])
            ->add('covenant', EntityType::class, [
                'label' => false,
                'class' => Covenant::class,
                'choice_label' => 'name'
            ])
            ->add('rank', EntityType::class, [
                'label' => false,
                'class' => Rank::class,
                'choice_label' => 'name'
            ])
            ->add('figs', EntityType::class, [
                'label' => false,
                'class' => Figs::class,
                'choice_label' => 'name'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Character::class,
            'csrf_protection' => false
        ));
    }
}