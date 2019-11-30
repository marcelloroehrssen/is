<?php

namespace App\Form;

use App\Entity\Elysium;
use App\Entity\Item;
use App\Repository\ElysiumRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemElysiumAssign extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('elysia', EntityType::class, [
            'label' => 'Scegli un Elyseum',
            'class' => Elysium::class,
            'placeholder' => '<Nessuno>',
            'query_builder' => function (ElysiumRepository $er) {
                return $er->createQueryBuilder('e')
                    ->where('e.date > :now')
                    ->orderBy('e.date', 'ASC')
                    ->setParameter('now', new \DateTime());
            },
            'choice_label' => function (Elysium $entity) {
                $event = $entity->getProposal()->current();
                $label = '';
                if ($event) {
                    $label = $event->getName();
                }
                return sprintf(
                    '[%s] %s',
                    $entity->getDate()->format('d-m-Y'), $label
                );
            },
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
            'csrf_protection' => false,
        ]);
    }
}