<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 19/05/2018
 * Time: 20:40
 */

namespace App\Form;


use App\Entity\Board;
use App\Entity\Character;
use App\Entity\Equipment;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentSend extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('receiver', EntityType::class, [
                'class' => Character::class,
                'label' => 'Segli a chi inviare l\'oggetto',
                'choice_label' => 'characterName',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Equipment::class,
            'csrf_protection' => false
        ));
    }
}