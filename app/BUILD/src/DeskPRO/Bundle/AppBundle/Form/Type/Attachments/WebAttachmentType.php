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
            $form->add('blob', WebAcceptAttachmentType::class, [
                'label' => false,
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
