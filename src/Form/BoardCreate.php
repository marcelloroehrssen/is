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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BoardCreate extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('author', EntityType::class, [
                'label' => 'Autore',
                'class' => Character::class,
                'choice_label' => 'characterName'
            ])
            ->add('title', TextType::class, ['label' => 'Titolo'])
            ->add('text', TextareaType::class, ['label' => 'Testo'])
            ->add('isCrypted', CheckboxType::class, ['label' => false])->setRequired(false)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Board::class,
            'csrf_protection' => false
        ));
    }
}