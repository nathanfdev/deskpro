<?php

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
