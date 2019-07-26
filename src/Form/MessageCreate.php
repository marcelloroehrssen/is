<?php
/**
 * Created by PhpStorm.
 * User: Marcello
 * Date: 17/11/2018
 * Time: 17:05.
 */

namespace App\Form;

use App\Entity\Character;
use App\Form\ValueObject\MessageVo;
use App\Repository\CharacterRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageCreate extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $selectOptions = [
            'class' => Character::class,
            'label' => 'Scegli il destinatario',
            'choice_label' => 'characterName',
        ];

        if (null === $options['character']) {
            $selectOptions['group_by'] = function (Character $choice, $key, $value) {
                if (Character::TYPE_PNG == $choice->getType()) {
                    return Character::TYPE_PNG;
                }

                return Character::TYPE_PG;
            };

            $builder->add('sender', EntityType::class, array_merge($selectOptions, ['label' => 'Scegli il mittente']));
        } else {
            $selectOptions['query_builder'] = function (CharacterRepository $er) use ($options) {
                return $er->getAllOthersQB($options['character']);
            };
        }

        $builder
            ->add('recipient', EntityType::class, $selectOptions)
            ->add('text', TextareaType::class, ['label' => 'Testo'])
                ->setRequired(false)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MessageVo::class,
            'character' => null,
            'csrf_protection' => false,
        ]);
    }
}
