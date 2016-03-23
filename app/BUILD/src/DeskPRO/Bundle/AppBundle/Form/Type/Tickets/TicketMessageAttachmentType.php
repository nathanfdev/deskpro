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
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TicketMessageAttachmentType.
 */
class TicketMessageAttachmentType extends AbstractType
{
    /**
     * @var \Application\DeskPRO\BlobStorage\DeskproBlobStorage
     */
    private $blob_storage;

    /**
     * @var BlobRepo
     */
    private $blob_repo;

    /**
     * @var AcceptAttachment
     */
    private $attachment_accepter;

    /**
     * Constructor.
     *
     * @param DeskproBlobStorage $blob_storage
     * @param BlobRepo           $blob_repo
     * @param AcceptAttachment   $attachment_accepter
     */
    public function __construct(DeskproBlobStorage $blob_storage, BlobRepo $blob_repo, AcceptAttachment $attachment_accepter)
    {
        $this->blob_storage        = $blob_storage;
        $this->blob_repo           = $blob_repo;
        $this->attachment_accepter = $attachment_accepter;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
        $form       = $event->getForm();
        $attachment = $event->getData();

        // setting initial value
        if (!$attachment instanceof TicketAttachment) {
            $attachment = new TicketAttachment();
        }

        $event->setData($attachment);

        // setting fields depend on initial value
        $blob = $attachment->getBlob();
        if ($blob && $blob->getAuthcode()) {
            $this->setAttachmentFieldsOnForm($form);
        } else {
            $this->setUploadFieldOnForm($form);
        }
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

        // Got blob auth code from the request
        // Trying to assign a real blob to attachment or delete it
        if (array_key_exists('blob_auth', $data)) {
            // handle deleting of the attachment
            if (array_key_exists('delete', $data) && $data['delete']) {
                $this->setUploadFieldOnForm($form);

                $blob = $attachment->getBlob();
                if ($blob) {
                    $this->blob_storage->deleteBlobRecord($blob);
                }

                $event->setData(null);

                return;
            }

            // find for existing blob by auth code
            $blob = $this->blob_repo->getByAuthCode($data['blob_auth']);

            // or just set empty blob to correct setting of form fields and form validation
            if (!$blob) {
                $blob = new Blob();
                $blob->setAuthCode('');
            }

            $attachment->setBlob($blob);
            $this->setAttachmentFieldsOnForm($form);
        } else {
            // Got uploaded file, try to accept and set blob auth code as form data
            $this->setUploadFieldOnForm($form);

            $file = isset($data['upload']) ? $data['upload'] : null;
            if ($file instanceof UploadedFile) {
                $error = $this->attachment_accepter->getError($file, 'user');

                // Unable to accept, generate error
                if ($error) {
                    $error_code = $error['error_code'];
                    $params     = [];

                    $error_detail = $error['error_detail'];
                    if ($error_detail) {
                        $params = ['detail' => $error_detail];
                    }

                    $phrase = sprintf('portal.forms.error_accept_%s', $error_code);
                    $form->get('upload')->addError(new FormError($phrase, $phrase, $params));
                } else {
                    $blob = $this->attachment_accepter->accept($file, true);
                    $attachment->setBlob($blob);

                    $this->setAttachmentFieldsOnForm($form);
                    $event->setData([
                        'blob_auth' => $blob->authcode,
                    ]);
                }
            }
        }
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
        $form           = $event->getForm();
        $config         = $form->getConfig();
        $person         = $config->getOption('person');
        $ticket_message = $config->getOption('ticket_message');
        $attachment     = $event->getData();

        if ($attachment instanceof TicketAttachment) {
            if ($attachment->getPerson() !== $person) {
                $attachment->setPerson($person);
            }

            $ticket_message->addAttachment($attachment);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ticket_message_attachment';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
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

    /**
     * @param FormInterface $form
     */
    private function setUploadFieldOnForm(FormInterface $form)
    {
        $form->add('upload', 'file', [
            'mapped'      => false,
            'required'    => false,
            'label'       => false,
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\File([
                    'uploadErrorMessage'         => 'portal.forms.error_upload_general',
                    'uploadFormSizeErrorMessage' => 'portal.forms.error_upload_html_size',
                    'uploadIniSizeErrorMessage'  => 'portal.forms.error_upload_ini_size',
                    'notFoundMessage'            => 'portal.forms.error_upload_general',
                    'notReadableMessage'         => 'portal.forms.error_upload_general',
                    'disallowEmptyMessage'       => 'portal.forms.error_upload_empty',
                ]),
            ],
        ]);

        foreach (['blob_auth', 'is_inline', 'delete'] as $field_to_remove) {
            if ($form->has($field_to_remove)) {
                $form->remove($field_to_remove);
            }
        }
    }

    /**
     * @param FormInterface $form
     */
    private function setAttachmentFieldsOnForm(FormInterface $form)
    {
        if ($form->has('upload')) {
            $form->remove('upload');
        }

        if (!$form->has('blob_auth')) {
            $form->add('blob_auth', 'hidden', [
                'property_path'  => 'blob.authcode',
                'error_bubbling' => false,
            ]);
        }
        if (!$form->has('is_inline')) {
            $form->add('is_inline', 'checkbox', [
                'required' => false,
            ]);
        }
        if (!$form->has('delete')) {
            $form->add('delete', 'checkbox', [
                'mapped'   => false,
                'required' => false,
            ]);
        }
    }
}
