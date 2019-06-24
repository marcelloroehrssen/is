<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 19/05/2018
 * Time: 20:40.
 */

namespace App\Form;

use App\Entity\Elysium;
use App\Repository\ElysiumRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\ElysiumProposal;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ElysiumProposalEdit extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('validity', EntityType::class, array(
                'label' => 'Scegli le date di validità della proposta',
                'class' => Elysium::class,
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (ElysiumRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.date > :now')
                        ->orderBy('e.date', 'ASC')
                        ->setParameter('now', new \DateTime());
                },
                'choice_label' => function(Elysium $entity) {
                    return $entity->getDate()->format('d-m-Y');
                },
            ))->setRequired(true)
            ->add('name', TextType::class, ['label' => 'Nome evento'])->setRequired(true)
            ->add('lineup', TextareaType::class, [
                'label' => '[OG] Scaletta (sarà visibile solo alla narrazione)',
                'attr' => [
                    'rows' => 5,
                ],
            ])->setRequired(true)
            ->add('description', TextareaType::class, [
                'label' => '[IG] Scrivi la tua intro IG (una volta approvata sarà visibile a tutti i giocatori)',
                'attr' => [
                    'rows' => 5,
                ],
            ])->setRequired(false)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ElysiumProposal::class,
            'csrf_protection' => false,
        ]);
    }
}
