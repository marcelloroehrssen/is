<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 13/05/2018
 * Time: 14:42.
 */

namespace App\Form;

use App\Entity\CharacterExtra;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacterCoverUploader extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cover', FileType::class)->setRequired(true)
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
