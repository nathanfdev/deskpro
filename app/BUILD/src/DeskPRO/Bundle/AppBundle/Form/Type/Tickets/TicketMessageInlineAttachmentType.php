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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Attachments\AcceptAttachment;
use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\EntityRepository\Blob as BlobRepo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * An inline attachment is one that is uploaded via pasting. So this field has no actual UI because
 * its only filled by JS.
 */
class TicketMessageInlineAttachmentType extends AbstractType
{
    /**
     * @var \Application\DeskPRO\BlobStorage\DeskproBlobStorage
     */
    private $blobStorage;

    /**
     * @var BlobRepo
     */
    private $blobRepo;

    /**
     * @var AcceptAttachment
     */
    private $attachmentAccepter;

    /**
     * Constructor.
     *
     * @param DeskproBlobStorage $blobStorage
     * @param BlobRepo           $blobRepo
     * @param AcceptAttachment   $attachmentAccepter
     */
    public function __construct(DeskproBlobStorage $blobStorage, BlobRepo $blobRepo, AcceptAttachment $attachmentAccepter)
    {
        $this->blobStorage        = $blobStorage;
        $this->blobRepo           = $blobRepo;
        $this->attachmentAccepter = $attachmentAccepter;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('blob_auth', 'hidden', [
            'property_path'  => 'blob.authcode',
            'error_bubbling' => false,
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetRelations']);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreData(FormEvent $event)
    {
        $attachment = $event->getData();

        // setting initial value
        if (!$attachment instanceof TicketAttachment) {
            $attachment = new TicketAttachment();

            $blob = new Blob();
            $blob->setAuthCode('');
            $attachment->setBlob($blob);
        }

        $event->setData($attachment);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (!is_array($data)) {
            return;
        }

        /** @var TicketAttachment $attachment */
        $attachment = $form->getData();

        // find for existing blob by auth code
        if (!empty($data['blob_auth'])) {
            $blob = $this->blobRepo->getByAuthCode($data['blob_auth']);
        } else {
            $blob = null;
        }

        // or just set empty blob to correct setting of form fields and form validation
        if (!$blob) {
            $blob = new Blob();
            $blob->setAuthCode('');
        }

        $attachment->setBlob($blob);
    }

    /**
     * Deletes the TicketAttachment record if it does not contain a valid
     * blob after all processing is done.
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $attachment = $event->getData();
        if ($attachment instanceof TicketAttachment && !$attachment->getBlob()) {
            $event->setData(null);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onSetRelations(FormEvent $event)
    {
        $form          = $event->getForm();
        $config        = $form->getConfig();
        $person        = $config->getOption('person');
        $ticketMessage = $config->getOption('ticket_message');
        $attachment    = $event->getData();

        if ($attachment instanceof TicketAttachment) {
            if ($attachment->getPerson() !== $person) {
                $attachment->setPerson($person);
            }

            $ticketMessage->addAttachment($attachment);
            $attachment->is_inline = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ticket_message_inline_attachment';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'     => TicketAttachment::class,
                'error_bubbling' => false,
            ])
            ->setRequired([
                'ticket_message',
                'person',
            ])
            ->setAllowedTypes([
                'ticket_message' => TicketMessage::class,
                'person'         => Person::class,
            ])
        ;
    }
}
