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

class CharacterCreate extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('characterName', TextType::class, [
                'label' => 'Nome personaggio'
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Tipo',
                'choices'  => array(
                    'PG' => 'PG',
                    'PNG' => 'PNG',
                ),
            ])
            ->add('clan', EntityType::class, [
                'label' => 'Clan',
                'class' => Clan::class,
                'choice_label' => 'name'
            ])
            ->add('covenant', EntityType::class, [
                'label' => 'Congrega',
                'class' => Covenant::class,
                'choice_label' => 'name'
            ])
            ->add('rank', EntityType::class, [
                'label' => 'Grado',
                'class' => Rank::class,
                'choice_label' => 'name'
            ])
            ->add('figs', EntityType::class, [
                'label' => 'Carica',
                'class' => Figs::class,
                'choice_label' => 'name'
            ])
            ->add('canCreateEdict', ChoiceType::class, [
                'label' => 'Può creare editti',
                'choices'  => array(
                    'si' => true,
                    'No' => false,
                ),
            ])
            ->add('cacophonySavy', ChoiceType::class, [
                'label' => 'Ha cacophony savy',
                'choices'  => array(
                    'NO' => 0,
                    'o' => 1,
                    'oo' => 2,
                    'ooo' => 3,
                ),
            ])
            ->add('extra', CharacterExtraCreate::class)
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