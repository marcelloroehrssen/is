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