<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Attachments;

use Application\DeskPRO\Entity\Blob;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class WebInlineAttachmentType.
 */
class WebInlineAttachmentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseAttachmentType::class;
    }

    /**
     * Prevent blob validation errors.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data) {
            return;
        }

        $blob = $data->getBlob();
        if (!$blob instanceof Blob || !$blob->getId()) {
            $event->setData(null);
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if ($data) {
            $data->setIsInline(true);
        }
    }
}
