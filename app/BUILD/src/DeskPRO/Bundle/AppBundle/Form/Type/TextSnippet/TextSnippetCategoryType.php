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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
            ->add('title', 'object_lang_collection', [
                'mapped'      => false,
                'prop_name'   => 'title',
                'owner'       => $builder->getData(),
                'constraints' => [
                    new Assert\Count(['min' => 1]),
                ],
            ])
            ->add('is_global', ApiBooleanType::class)
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetTypeAndPerson']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TextSnippetCategory::class,
            ])
            ->setRequired(['type', 'person'])
            ->setAllowedTypes([
                'person' => Person::class,
            ])
            ->setAllowedValues([
                'type' => [
                    TextSnippetCategory::TYPE_TICKET,
                    TextSnippetCategory::TYPE_CHAT,
                ],
            ])
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onSetTypeAndPerson(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $event->getForm()->getConfig();

        $person = $config->getOption('person');
        $type   = $config->getOption('type');

        /** @var TextSnippetCategory $data */
        $data = $form->getData();
        $data
            ->setTypename($type)
            ->setPerson($person)
        ;
    }
}
