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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Snippets;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SnippetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', PersonAssignType::class, [
                'person' => $options['person'],
            ])
            ->add('title', TextType::class)
            ->add('labels', CollectionType::class, [
                'entry_type'     => SnippetLabelType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
                'options'        => [
                    'error_bubbling' => true,
                'snippet'            => $builder->getData(),
                ],
                'by_reference' => false,
            ])
            ->add('types', ChoiceType::class, [
                'multiple'          => true,
                'choices_as_values' => true,
                'choices'           => ['chat', 'ticket'],
            ])
            ->add('translations', CollectionType::class, [
                'entry_type'     => SnippetTranslationType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
                'options'        => [
                    'error_bubbling' => true,
                    'snippet'        => $builder->getData(),
                ],
                'by_reference' => false,
            ])
            ->add('shortcut_code', TextType::class)
            ->add('is_draft', ApiBooleanType::class)
            ->add('is_ownership_global', ApiBooleanType::class)
            ->add('is_visible_global', ApiBooleanType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('person')
            ->setDefaults([
                'data_class' => Snippet::class,
            ])
            ->setAllowedTypes('person', Person::class)
        ;
    }
}
