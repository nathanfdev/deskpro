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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\ObjectLang;

use Application\DeskPRO\Entity\ObjectLang;
use DeskPRO\Bundle\AppBundle\Entity\ObjectTranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ObjectLangCollectionType.
 */
class ObjectLangCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onLoadData']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onMergeData']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['prop_name', 'owner'])
            ->addAllowedTypes([
                'prop_name' => 'string',
                'owner'     => ObjectTranslatableInterface::class,
            ])
            ->setDefaults([
                'mapped'         => false,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
                'entry_type'     => ObjectLangType::class,
                'entry_options'  => function (Options $options) {
                    return [
                        'prop_name' => $options['prop_name'],
                        'owner'     => $options['owner'],
                    ];
                },
            ])
        ;
    }

    /**
     * Load related entity property translations.
     *
     * @param FormEvent $event
     */
    public function onLoadData(FormEvent $event)
    {
        $context = new ObjectLangContext($event);
        $owner   = $context->getOwner();

        if ($owner->getId()) {
            $data = $owner->getObjectPropTranslations($context->getPropName());
        } else {
            $data = new ArrayCollection();
        }

        $event->setData($data);
    }

    /**
     * Set updated translations.
     * Will be saved after entity persist via object translatable lifecycle callback.
     *
     * @param FormEvent $event
     */
    public function onMergeData(FormEvent $event)
    {
        $context = new ObjectLangContext($event);
        $owner   = $context->getOwner();

        $all_objects  = $owner->getObjectPropsTranslations();
        $prop_objects = $event->getData();

        /** @var ObjectLang $object_lang */
        foreach ($prop_objects as $object_lang) {
            if (!$all_objects->contains($object_lang)) {
                $all_objects->add($object_lang);
            }
        }
        foreach ($all_objects as $object_lang) {
            if ($object_lang->getPropName() === $context->getPropName() && !$prop_objects->contains($object_lang)) {
                $all_objects->removeElement($object_lang);
            }
        }

        $event->setData($all_objects);
    }
}
