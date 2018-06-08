<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 19/05/2018
 * Time: 20:40
 */

namespace App\Form;


use App\Entity\Downtime;
use App\Entity\Merits;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DowntimeAdd extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titolo'])
            ->add('text', TextareaType::class, ['label' => 'Testo'])
            ->add('isHunt', HiddenType::class, ['label' => false])
            ->add('associatedMerits', EntityType::class, [
                'placeholder' => 'Scegli un merito se vuoi',
                'empty_data'  => null,
                'required' => false,
                'label' => 'Merito',
                'class' => Merits::class,
                'choice_label' => 'name'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Downtime::class,
            'csrf_protection' => false
        ));
    }
}
