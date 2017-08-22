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

namespace DeskPRO\Bundle\AppBundle\Form\CustomFieldManager;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FieldNameResolver implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'onSubmitResolveFieldNames'
        ];
    }

    /**
     * @param array|FieldNameResolvingStrategy[] $otherStrategies
     * @return FieldNameResolver
     */
    public static function createWitDefaultStrategies(array $otherStrategies)
    {
        $defaultStrategies = [
            new LegacyFieldNameResolvingStrategy()
        ];

        $strategies = array_merge([], $otherStrategies, $defaultStrategies);
        return new FieldNameResolver($strategies);
    }

    /** @var FieldNameResolvingStrategy[] */
    private $strategies;

    /**
     * @param array|FieldNameResolvingStrategy[] $strategies
     */
    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmitResolveFieldNames(FormEvent $event)
    {
        $fieldsPath = 'fields';
        $data = $event->getData();

        $fields = is_array($data) && array_key_exists($fieldsPath, $data) ? $data[$fieldsPath] : null;
        if (! is_array($fields)) {
            return;
        }
        $resolvedFields = [];
        foreach ($fields as $name => $value) {
            $resolvedName = $this->resolveField($name);
            $resolvedFields[$resolvedName] = $value;
        }
        $data[$fieldsPath] = $resolvedFields;
        $event->setData($data);
    }

    private function resolveField($fieldName)
    {
        $resolvedNames = [];
        foreach($this->strategies as $strategy) {
            $resolvedNames[] = $strategy->resolve($fieldName);
        }

        $resolvedNames = array_values(array_filter($resolvedNames, "is_string"));

        if (count($resolvedNames) === 0) { // identity strategy if no strategy applied
            return $fieldName;
        }

        if (count($resolvedNames) === 1) { // only one strategy resolved the field name
            return $resolvedNames[0];
        }

        // more than one strategy resolved the field name, this is a problem
        throw FieldNameResolverException::ambiguousNameResolution($fieldName, $resolvedNames);
    }
}
