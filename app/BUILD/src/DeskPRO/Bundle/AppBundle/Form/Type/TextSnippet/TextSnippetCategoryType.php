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

use Application\DeskPRO\Domain\ObjectTranslatable;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TextSnippetCategory;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('title', TextType::class, [
                'error_bubbling' => false,
            ])
            ->add('is_global', ApiBooleanType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onSetObjectTranslatable']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetTypeAndPerson']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'    => TextSnippetCategory::class,
                'error_mapping' => [
                    'translatedTitle' => 'title',
                ],
            ])
            ->setRequired(['type', 'person'])
            ->setAllowedTypes([
                'person' => Person::class,
            ])
            ->setAllowedValues([
                'type' => [TextSnippetCategory::TYPE_TICKET, TextSnippetCategory::TYPE_CHAT],
            ])
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onSetObjectTranslatable(FormEvent $event)
    {
        /** @var Person $person */
        $person = $event->getForm()->getConfig()->getOption('person');

        $object_translatable = ObjectTranslatable::loadObjectTranslatable($event->getData());
        $object_translatable->setLang($person->getLanguage());
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
