<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 14/05/2018
 * Time: 14:45.
 */

namespace App\Form;

use App\Entity\CharacterExtra;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacterExtraCreate extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titolo',
            ])->setRequired(false)
            ->add('city', TextType::class, [
                'label' => 'Citt�',
            ])->setRequired(false)
            ->add('bio', TextareaType::class)->setRequired(false)
            ->add('quote', TextareaType::class)->setRequired(false)
            ->add('cite', TextType::class)->setRequired(false)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CharacterExtra::class,
            'csrf_protection' => false,
        ]);
    }
}
