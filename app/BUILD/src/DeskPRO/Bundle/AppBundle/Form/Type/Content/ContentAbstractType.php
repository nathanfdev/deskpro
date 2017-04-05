<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\Content;

use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add(
                'language',
                EntityType::class,
                [
                    'class'        => Language::class,
                    'choice_label' => 'title',
                ]
            )
            ->add(
                'person',
                EntityType::class,
                [
                    'class'        => Person::class,
                    'choice_label' => 'name',
                ]
            )
            ->add(
                'content_input_type',
                ChoiceType::class,
                [
                    'multiple' => false,
                    'expanded' => false,
                    'choices'  => [
                        ContentAbstract::CONTENT_TYPE_MARKDOWN => 'Markdown',
                        ContentAbstract::CONTENT_TYPE_RTE      => 'RTE',
                    ],
                ]
            )
            ->add(
                'status',
                ChoiceType::class,
                [
                    'multiple' => false,
                    'expanded' => false,
                    'choices'  => [
                        ContentAbstract::STATUS_ARCHIVED  => ContentAbstract::STATUS_ARCHIVED,
                        ContentAbstract::STATUS_HIDDEN    => ContentAbstract::STATUS_HIDDEN,
                        ContentAbstract::STATUS_PUBLISHED => ContentAbstract::STATUS_PUBLISHED,
                    ],
                ]
            )
            ->add(
                'hidden_status',
                ChoiceType::class,
                [
                    'multiple' => false,
                    'expanded' => false,
                    'choices'  => [
                        ContentAbstract::HIDDEN_STATUS_DELETED     => ContentAbstract::HIDDEN_STATUS_DELETED,
                        ContentAbstract::HIDDEN_STATUS_DRAFT       => ContentAbstract::HIDDEN_STATUS_DRAFT,
                        ContentAbstract::HIDDEN_STATUS_PENDING     => ContentAbstract::HIDDEN_STATUS_PENDING,
                        ContentAbstract::HIDDEN_STATUS_SPAM        => ContentAbstract::HIDDEN_STATUS_SPAM,
                        ContentAbstract::HIDDEN_STATUS_UNPUBLISHED => ContentAbstract::HIDDEN_STATUS_UNPUBLISHED,
                    ],
                ]
            );
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data' => true,
        ]);
    }
}
