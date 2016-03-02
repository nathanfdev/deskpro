<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Form\Type\TextSnippet;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TextSnippetCategory;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TextSnippetCategoryType.
 */
class TextSnippetCategoryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', EntityType::class, [
                'class' => Person::class,
            ])
            ->add('is_global', ApiBooleanType::class)
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetType']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(['type'])
            ->setAllowedValues([
                'type' => [TextSnippetCategory::TYPE_TICKET, TextSnippetCategory::TYPE_CHAT],
            ])
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onSetType(FormEvent $event)
    {
        $form = $event->getForm();
        $type = $form->getConfig()->getOption('type');

        /** @var TextSnippetCategory $data */
        $data = $form->getData();
        $data->setTypename($type);
    }
}
