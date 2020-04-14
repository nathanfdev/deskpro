<?php

namespace DeskPRO\Bundle\ApiBundle\Form\Type\Batch;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class GetBatchRequestType.
 */
class GetBatchRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('get', BatchRequestsType::class, [
            'required' => true,
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (isset($data['get']) && is_string($data['get'])) {
            $data['get'] = explode(',', $data['get']);
        }

        $event->setData($data);
    }
}
