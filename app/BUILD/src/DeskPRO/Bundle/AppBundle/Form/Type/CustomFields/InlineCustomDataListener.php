<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\CustomFields;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class InlineCustomDataListener.
 */
class InlineCustomDataListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'onSetInlineData',
        ];
    }

    /**
     * Set form data from inline value.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetInlineData(FormEvent $event)
    {
        $data = $event->getData();

        // default format based on form "data" field
        if (isset($data['data'])) {
            return;
        }

        // custom data serializer format we get from api response
        if (isset($data['value'])) {
            $data = $data['value'];
        }

        $event->setData([
            'data' => $data,
        ]);
    }
}
