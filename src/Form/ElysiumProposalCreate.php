<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 19/05/2018
 * Time: 20:40
 */

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\ElysiumProposal;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ElysiumProposalCreate extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nome evento'])->setRequired(true)
            ->add('lineup', TextareaType::class, [
                'label' => 'Scaletta (sarà visibile solo alla narrazione)',
                'attr' => [
                    'rows' => 5
                ]
            ])->setRequired(true)
            ->add('description', TextareaType::class, [
                'label' => 'Scrivi la tua intro IG (una volta approvata sarà visibile a tutti i giocatori)',
                'attr' => [
                    'rows' => 5
                ]
            ])->setRequired(false)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ElysiumProposal::class,
            'csrf_protection' => false
        ));
    }
}