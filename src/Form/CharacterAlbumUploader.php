<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 13/05/2018
 * Time: 14:42
 */

namespace App\Form;


use App\Entity\Character;
use App\Entity\CharacterExtra;
use App\Entity\CharacterPhoto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacterAlbumUploader extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('path', FileType::class)->setRequired(true)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CharacterPhoto::class,
            'csrf_protection' => false
        ));
    }
}