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
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TopicType.
 */
class TopicType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('slug', TextType::class, [
                'required' => false,
            ])
            ->add('display_order', TextType::class, [
                'required' => false,
            ])
            ->add('content', TextareaType::class, [
                'required' => true,
            ])
            ->add('content_input', TextareaType::class, [
                'required' => false,
            ])
            ->add('content_input_type', ChoiceType::class, [
                'required'          => false,
                'choices_as_values' => true,
                'choices'           => [
                    ContentAbstract::CONTENT_TYPE_RTE,
                    ContentAbstract::CONTENT_TYPE_MARKDOWN,
                ],
            ])
            ->add('status', ChoiceType::class, [
                'required'          => false,
                'choices_as_values' => true,
                'choices'           => [
                    ContentAbstract::STATUS_PUBLISHED,
                    ContentAbstract::STATUS_ARCHIVED,
                    ContentAbstract::STATUS_HIDDEN,
                ],
            ])
            ->add('guide', EntityType::class, [
                'class'    => Guide::class,
                'required' => true,
            ])
            ->add('parent', EntityType::class, [
                'class'    => Topic::class,
                'required' => false,
            ])
            ->add('author', PersonAssignType::class, [
                'property_path' => 'person',
                'person'        => $options['person'],
                'required'      => false,
            ])
            ->add('language', EntityType::class, [
                'class'    => Language::class,
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('person')
            ->setDefaults([
                'data_class' => Topic::class,
            ])
            ->setAllowedTypes('person', Person::class)
        ;
    }
}
