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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments;

use Application\DeskPRO\Attachments\AcceptAttachment;
use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\EntityRepository\Blob as BlobRepo;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketMessageAttachmentType.
 */
class TicketMessageAttachmentType extends AbstractType
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
    private $acceptAttachment;

    /**
     * Constructor.
     *
     * @param DeskproBlobStorage $blobStorage
     * @param BlobRepo           $blobRepo
     * @param AcceptAttachment   $acceptAttachment
     */
    public function __construct(DeskproBlobStorage $blobStorage, BlobRepo $blobRepo, AcceptAttachment $acceptAttachment)
    {
        $this->blobStorage      = $blobStorage;
        $this->blobRepo         = $blobRepo;
        $this->acceptAttachment = $acceptAttachment;
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
                    $this->blobStorage->deleteBlobRecord($blob);
                }

                $event->setData(null);

                return;
            }

            // find for existing blob by auth code
            $blob = $this->blobRepo->getByAuthCode($data['blob_auth']);

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
                $error = $this->acceptAttachment->getError($file, 'user');

                // Unable to accept, generate error
                if ($error) {
                    $errorCode = $error['error_code'];
                    $params    = [];

                    $errorDetail = $error['error_detail'];
                    if ($errorDetail) {
                        $params = ['detail' => $errorDetail];
                    }

                    $phrase = sprintf('portal.forms.error_accept_%s', $errorCode);
                    $form->get('upload')->addError(new FormError($phrase, $phrase, $params));
                } else {
                    $blob = $this->acceptAttachment->accept($file, true);
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
        $form       = $event->getForm();
        $config     = $form->getConfig();
        $person     = $config->getOption('person');
        $message    = $config->getOption('ticket_message');
        $attachment = $event->getData();

        if ($attachment instanceof TicketAttachment) {
            if ($attachment->getPerson() !== $person) {
                $attachment->setPerson($person);
            }

            $message->addAttachment($attachment);

            if (!$attachment->getBlob()->getId()) {
                $form->addError(new FormError(ErrorsCodes::NO_UPLOADED_FILE));
            }
        }
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
                'constraints'    => [
                    // check message attachments directly via the form to prevent checking all ticket messages collection
                    new Assert\Valid(),
                ],
            ])
            ->setRequired([
                'ticket_message',
                'person',
            ])
            ->setAllowedTypes('ticket_message', TicketMessage::class)
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * @param FormInterface $form
     */
    private function setUploadFieldOnForm(FormInterface $form)
    {
        $form->add('upload', FileType::class, [
            'mapped'      => false,
            'required'    => false,
            'label'       => false,
            'constraints' => [
                new File([
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
            $form->add('blob_auth', HiddenType::class, [
                'property_path'  => 'blob.authcode',
                'error_bubbling' => false,
            ]);
        }
        if (!$form->has('is_inline')) {
            $form->add('is_inline', CheckboxType::class, [
                'required' => false,
            ]);
        }
        if (!$form->has('delete')) {
            $form->add('delete', CheckboxType::class, [
                'mapped'   => false,
                'required' => false,
            ]);
        }
    }
}
