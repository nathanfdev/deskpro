<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Form\Type;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\TicketAttachment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TicketMessageAttachmentType extends AbstractType
{
    /**
     * @var \Application\DeskPRO\BlobStorage\DeskproBlobStorage
     */
    private $blob_storage;

    public function __construct(DeskproBlobStorage $blob_storage)
    {
        $this->blob_storage = $blob_storage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event){
            /** @var \Application\DeskPRO\Entity\TicketAttachment $attachment */
            $attachment = $event->getData() instanceof TicketAttachment ? $event->getData() : new TicketAttachment();
            $form = $event->getForm();

            if (!$attachment->getBlob()) {
                $form->add('upload', 'file', array('mapped' => false, 'required' => false, 'label' => false));
            } else {
                $form->add('blob_auth', 'hidden', array('property_path' => 'blob.authcode'));
                $form->add('delete', 'checkbox', array('mapped' => false, 'required' => false));
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, array($this, 'postSubmit'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'preSubmit'));
    }

    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $submittedData = $event->getData();
        if (array_key_exists('blob_auth', $submittedData)) {
            if (!$form->getData()) {
                $attachment = new TicketAttachment();
                $form->setData($attachment);
            }
            $form->getData()->setBlob($this->blob_storage->getBlobEntityFromAuthcode($submittedData['blob_auth']));

            // delete?
            if (array_key_exists('delete', $submittedData)) {
                if ($submittedData['delete'] != 0) {
                    $attachment = $form->getData();
                    if ($blob = $attachment->getBlob()) {
                        $this->blob_storage->deleteBlobRecord($blob);
                    }
                    $form->setData(null);
                    $form->remove('blob_auth');
                    $form->remove('delete');
                    $form->add('upload', 'file', array('mapped' => false, 'required' => false, 'label' => false));
                }
            } else {
                if ($form->has('upload')) {
                    $form->remove('upload');
                }
                if (!$form->has('blob_auth')) {
                    $form->add('blob_auth', 'hidden', array('property_path' => 'blob.authcode'));
                }
            }
        }
    }

    public function postSubmit(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\TicketAttachment $attachment */
        $attachment = $event->getData() instanceof TicketAttachment ? $event->getData() : new TicketAttachment();
        $form = $event->getForm();
        $person = $form->getConfig()->getOption('person');
        $ticket_message = $form->getConfig()->getOption('ticket_message');

        if ($form->has('upload')) {
            $file = $form->get('upload')->getData();

            if ($file instanceof File && $file->getRealPath()) {
                $blob = $this->blob_storage->createBlobRecordFromFile(
                    $file->getRealPath(),
                    $file->getClientOriginalName(),
                    $file->getClientMimeType()
                );

                $attachment->setBlob($blob);
                $attachment->setPerson($person);

                $ticket_message->addAttachment($attachment);

                $form->remove('upload');
                $form->add('delete', 'checkbox', array('mapped' => false, 'required' => false));
                $form->add('blob_auth', 'hidden', array('property_path' => 'blob.authcode'));
            } else {
                $ticket_message->attachments->removeElement($attachment);
            }
        } else {
            if (!$form->has('blob_auth')) {
                $form->add('blob_auth', 'hidden', array('property_path' => 'blob.authcode'));
                $ticket_message->addAttachment($attachment);
            }
        }

        if ($attachment->getPerson() != $person) {
            $attachment->setPerson($person);
        }
        $ticket_message->addAttachment($attachment);
        $form->setData($attachment);
    }

    public function getName()
    {
        return 'ticket_message_attachment';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Application\\DeskPRO\\Entity\\TicketAttachment'
            )
        );

        $resolver->setRequired(
            array(
                'ticket_message',
                'person'
            )
        );

        $resolver->setAllowedTypes(
            array(
                'ticket_message' => 'Application\\DeskPRO\\Entity\\TicketMessage',
                'person' => 'Application\\DeskPRO\\Entity\\Person'
            )
        );
    }
}
 