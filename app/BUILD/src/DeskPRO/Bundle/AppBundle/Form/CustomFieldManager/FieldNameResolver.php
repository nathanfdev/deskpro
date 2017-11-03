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
use DeskPRO\Bundle\AppBundle\ObjectAlias;

/**
 * This class iterates through a list of alias resolving strategies until it encounters the first strategy that resolves
 * the alias. If no such strategy exists it returns the original alias
 */
class FieldNameResolver implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'onSubmitResolveFieldNames'
        ];
    }

    /** @var ObjectAlias\FieldIdResolvingStrategy[] */
    private $strategies;

    /**
     * @param array|ObjectAlias\FieldIdResolvingStrategy[] $strategies an ordered list of strategies
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
        $resolvedName = null;
        foreach($this->strategies as $strategy) {
            $resolvedName = $strategy->resolve($fieldName);
            if (!empty($resolvedName)) {
                break;
            }
        }

        // identity strategy if no strategy resolved the alias
        if (empty($resolvedName)) {
            return $fieldName;
        }

        return $resolvedName;
    }
}
