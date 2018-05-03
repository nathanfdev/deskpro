<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Content;

use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Language;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ContentAbstractType.
 */
class ContentAbstractType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('content', TextareaType::class)
            ->add('person', PersonAssignType::class, ['required' => false])
            ->add('language', EntityType::class, [
                'class'    => Language::class,
                'required' => false,
            ])
            ->add('content_input_type', ChoiceType::class, [
                'required'          => false,
                'multiple'          => false,
                'expanded'          => false,
                'choices_as_values' => true,
                'choices'           => [
                    ContentAbstract::CONTENT_TYPE_MARKDOWN,
                    ContentAbstract::CONTENT_TYPE_RTE,
                ],
            ])
            ->add('status', ChoiceType::class, [
                'multiple'          => false,
                'expanded'          => false,
                'choices_as_values' => true,
                'choices'           => [
                    ContentAbstract::STATUS_ARCHIVED,
                    ContentAbstract::STATUS_PUBLISHED,
                    ContentAbstract::STATUS_HIDDEN.'.'.ContentAbstract::HIDDEN_STATUS_DELETED,
                    ContentAbstract::STATUS_HIDDEN.'.'.ContentAbstract::HIDDEN_STATUS_DRAFT,
                    ContentAbstract::STATUS_HIDDEN.'.'.ContentAbstract::HIDDEN_STATUS_PENDING,
                    ContentAbstract::STATUS_HIDDEN.'.'.ContentAbstract::HIDDEN_STATUS_SPAM,
                    ContentAbstract::STATUS_HIDDEN.'.'.ContentAbstract::HIDDEN_STATUS_UNPUBLISHED,
                ],
            ])
        ;
    }
}
