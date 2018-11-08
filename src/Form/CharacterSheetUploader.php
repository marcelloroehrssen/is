<?php

namespace App\Form;


use App\Entity\Character;
use App\Entity\CharacterExtra;
use App\Entity\CharacterPhoto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacterSheetUploader extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sheet', FileType::class)->setRequired(true)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CharacterExtra::class,
            'csrf_protection' => false
        ));
    }
}