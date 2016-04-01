<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\Task;

use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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

    public function __construct($type, array $options = array())
    {
        $this->type = $type;
        $this->options = $options;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
            // (MergeCollectionListener, MergeDoctrineCollectionListener)
            FormEvents::SUBMIT => array('onSubmit', 50),
        );
    }

    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = array();
        }

        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        // First remove all rows
        foreach ($form as $name => $child) {
            $form->remove($name);
        }

        /** @var PersistentCollection $data */
        $meta = $data->getTypeClass();
        $mappings = $meta->associationMappings;
        unset($mappings['task']);
        $property = key($mappings);

        // Then add all rows again in the correct order
        foreach ($data as $name => $value) {
            $id = $meta->getReflectionProperty($property)->getValue($value)->getId();
            $form->add($id, $this->type, array_replace(array(
                'property_path' => '['.$name.']',
            ), $this->options));
        }
    }

    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data || '' === $data) {
            $data = array();
        }

        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            $data = array();
        }

        /** @var PersistentCollection $collection */
        $collection = $form->getData();
        $taskId = $collection->getOwner()->getId();

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
                $name = 'new' . $value;
                $form->add($name, $this->type, array_replace(array(
                    'property_path' => '['.$name.']',
                ), $this->options));
            }

            $transformed[$name] = [
                'task' => $taskId,
                'item' => $value,
            ];
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
            $data = array();
        }

        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        /** @var PersistentCollection $data */
        $meta = $data->getTypeClass();
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
        $toDelete = array();

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
