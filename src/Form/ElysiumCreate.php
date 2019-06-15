<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 19/05/2018
 * Time: 20:40.
 */

namespace App\Form;

use App\Form\ValueObject\ElysiumCreateVo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ElysiumCreate extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locationName', TextType::class, ['label' => false, 'attr' => ['placeholder' => 'Nome Location']])->setRequired(true)
            ->add('address', TextType::class, ['label' => false, 'attr' => ['placeholder' => 'Indirizzo']])->setRequired(true)
            ->add('date', DateTimeType::class, [
                    'label' => false,
                    'widget' => 'single_text',
                    'attr' => [
                        'min' => (new \DateTime())->format('Y-m-d'),
                    ],
                ])
                ->setRequired(true)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElysiumCreateVo::class,
            'csrf_protection' => false,
        ]);
    }
}
