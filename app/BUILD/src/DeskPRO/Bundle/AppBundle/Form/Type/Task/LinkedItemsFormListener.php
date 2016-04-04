<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Task;

use Doctrine\ORM\PersistentCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Resize a collection form element based on the data sent from the client.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class LinkedItemsFormListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $options;

    public function __construct($type, array $options = [])
    {
        $this->type    = $type;
        $this->options = $options;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preSubmit',
            // (MergeCollectionListener, MergeDoctrineCollectionListener)
            FormEvents::SUBMIT => ['onSubmit', 50],
        ];
    }

    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = [];
        }

        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        // First remove all rows
        foreach ($form as $name => $child) {
            $form->remove($name);
        }

        /* @var PersistentCollection $data */
        $meta     = $data->getTypeClass();
        $mappings = $meta->associationMappings;
        unset($mappings['task']);
        $property = key($mappings);

        // Then add all rows again in the correct order
        foreach ($data as $name => $value) {
            $id = $meta->getReflectionProperty($property)->getValue($value)->getId();
            $form->add($id, $this->type, array_replace([
                'property_path' => '['.$name.']',
            ], $this->options));
        }
    }

    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data || '' === $data) {
            $data = [];
        }

        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            $data = [];
        }

        /** @var PersistentCollection $collection */
        $collection = $form->getData();
        $taskId     = $collection->getOwner()->getId();

        // Remove all empty rows
        foreach ($form as $name => $child) {
            if (!in_array($name, $data)) {
                $form->remove($name);
            }
        }

        $transformed = [];
        // Add all additional rows
        foreach ($data as $name => $value) {
            $name = $value;
            if (!$form->has($name)) {
                $name = 'new'.$value;
                $form->add($name, $this->type, array_replace([
                    'property_path' => '['.$name.']',
                ], $this->options));
            }

            $transformed[$name] = $value;
        }

        $event->setData($transformed);
    }

    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        // At this point, $data is an array or an array-like object that already contains the
        // new entries, which were added by the data mapper. The data mapper ignores existing
        // entries, so we need to manually unset removed entries in the collection.

        if (null === $data) {
            $data = [];
        }

        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        /* @var PersistentCollection $data */
        $meta     = $data->getTypeClass();
        $mappings = $meta->associationMappings;
        unset($mappings['task']);
        $property = key($mappings);

        foreach ($form as $name => $child) {
            if ($child->isEmpty()) {
                unset($data[$name]);
                $form->remove($name);
            }
        }

        // The data mapper only adds, but does not remove items, so do this
        // here
        $toDelete = [];

        foreach ($data as $name => $child) {
            $id = $meta->getReflectionProperty($property)->getValue($child)->getId();
            if (!$form->has($name) && !$form->has($id)) {
                $toDelete[] = $name;
            }
        }

        foreach ($toDelete as $name) {
            unset($data[$name]);
        }

        $event->setData($data);
    }
}
