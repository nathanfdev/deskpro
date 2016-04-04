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

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SetCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit'], 50);
    }

    public function onPreSetData(FormEvent $event)
    {
        $acc  = PropertyAccess::createPropertyAccessor();
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = [];
        }

        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        $config  = $form->getConfig();
        $path    = $config->getOption('entry_property_path');
        $type    = $config->getOption('entry_type');
        $options = $config->getOption('entry_options');

        foreach ($data as $name => $value) {
            $childName = $path ? $acc->getValue($value, $path) : $value;
            $form->add($childName, $type, array_replace(['property_path' => '['.$name.']'], $options));
        }
    }

    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData() ?: [];
        $form = $event->getForm();

        foreach ($form as $name => $child) {
            if (!in_array($name, $data)) {
                $form->remove($name);
            }
        }

        $config      = $form->getConfig();
        $type        = $config->getOption('entry_type');
        $options     = $config->getOption('entry_options');
        $transformed = [];

        foreach ($data as $v) {
            $name = $v;
            if (!$form->has($v)) {
                $name = 'new_'.$v;
                $form->add($name, $type, array_replace(['property_path' => '['.$name.']'], $options));
            }
            $transformed[$name] = $v;
        }
        $event->setData($transformed);
    }

    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        foreach ($form as $name => $child) {
            if ($child->isEmpty()) {
                unset($data[$name]);
                $form->remove($name);
            }
        }

        $toDelete = [];
        $acc      = PropertyAccess::createPropertyAccessor();
        $config   = $form->getConfig();
        $path     = $config->getOption('entry_property_path');

        foreach ($data as $name => $child) {
            if (!$form->has($name) && !$form->has($acc->getValue($child, $path))) {
                $toDelete[] = $name;
            }
        }

        foreach ($toDelete as $name) {
            unset($data[$name]);
        }

        $event->setData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired([
            'entry_property_path', 'entry_type', 'entry_options',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'set_collection';
    }
}
