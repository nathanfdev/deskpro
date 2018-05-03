<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\Entity\FeedbackComment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FeedbackCommentType.
 */
class FeedbackCommentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', TextType::class, [
                'description' => 'text representation of comment status',
                'required'    => false,
            ])
            ->add('is_reviewed', ApiBooleanType::class, [
                'required'    => false,
                'description' => 'is comment was reviewed',
            ])->add('content', TextType::class, [
                'required'    => false,
                'description' => 'comment message',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FeedbackComment::class,
        ]);
    }
}
