<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Snippets;

use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Form\Type\JsonArrayType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SnippetMassActionType.
 */
class SnippetMassActionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('action', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => [
                    'labels',
                    'visibility',
                    'ownership',
                    'type',
                    'draft',
                    ],
            ])
            ->add('selected', EntityType::class, [
                'class'       => Snippet::class,
                'multiple'    => true,
                'constraints' => [
                    new Assert\Count(['min' => 1]),
                ],
            ])
            ->add('value', JsonArrayType::class)
        ;
    }
}
