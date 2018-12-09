<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 17/11/2018
 * Time: 17:05
 */

namespace App\Form;


use App\Entity\Character;
use App\Entity\Message;
use App\Form\ValueObject\LetterVo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LetterCreate extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('recipient', EntityType::class, [
                'class' => Character::class,
                'label' => 'Scegli il destinatario',
                'choice_label' => 'characterName',
            ])
            ->add('text', TextareaType::class, ['label' => 'Testo'])
                ->setRequired(false)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => LetterVo::class,
            'csrf_protection' => false
        ));
    }
}