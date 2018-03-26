<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Attachments;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Class WebAttachmentType.
 */
class WebAttachmentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
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
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($data->getBlob()) {
            $this->renderDeleteCheckbox($form);
        } else {
            $form->add('blob', AcceptAttachmentType::class, [
                'label'          => false,
                'field_name'     => 'upload',
                'upload_context' => 'user',
            ]);
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        // handle deleting of the attachment
        if (is_array($data) && array_key_exists('delete', $data) && $data['delete']) {
            $event->setData(null);
        }

        // if we have blob from blob_auth then remove blob accepter
        if ($form->getData() && $form->getData()->getBlob()) {
            if ($form->has('blob')) {
                $form->remove('blob');
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($data && $data->getBlob()) {
            if ($form->has('blob')) {
                $form->remove('blob');
            }

            $this->renderDeleteCheckbox($form);
        }
    }

    /**
     * @param FormInterface $form
     */
    private function renderDeleteCheckbox(FormInterface $form)
    {
        if ($form->has('delete')) {
            return;
        }

        $form->add('delete', CheckboxType::class, [
            'mapped'   => false,
            'required' => false,
        ]);
    }
}
