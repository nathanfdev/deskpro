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

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Attachments\AcceptAttachment;
use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\FeedbackAttachment;
use Application\DeskPRO\EntityRepository\Blob as BlobRepo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FeedbackAttachmentType.
 */
class FeedbackAttachmentType extends AbstractType
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Application\DeskPRO\Entity\TicketAttachment $attachment */
            $attachment = $event->getData() instanceof FeedbackAttachment ? $event->getData() : new FeedbackAttachment();
            $form = $event->getForm();

            if (!$attachment->getBlob()) {
                $this->addUpload($form);
            } else {
                $form->add('blob_auth', 'hidden', ['property_path' => 'blob.authcode']);
                $form->add('delete', 'checkbox', ['mapped' => false, 'required' => false]);
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'postSubmit']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
    }

    /**
     * @param FormInterface $form
     */
    public function addUpload(FormInterface $form)
    {
        $form->add(
            'upload',
            'file',
            [
                'mapped'      => false,
                'required'    => false,
                'label'       => false,
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\File(
                        [
                            'uploadErrorMessage'         => 'portal.forms.error_upload_general',
                            'uploadFormSizeErrorMessage' => 'portal.forms.error_upload_html_size',
                            'uploadIniSizeErrorMessage'  => 'portal.forms.error_upload_ini_size',
                            'notFoundMessage'            => 'portal.forms.error_upload_general',
                            'notReadableMessage'         => 'portal.forms.error_upload_general',
                            'disallowEmptyMessage'       => 'portal.forms.error_upload_empty',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form          = $event->getForm();
        $submittedData = $event->getData();
        if (array_key_exists('blob_auth', $submittedData)) {
            if (!$form->getData()) {
                $attachment = new FeedbackAttachment();
                $form->setData($attachment);
            }
            $form->getData()->setBlob($this->blob_repo->getByAuthCode($submittedData['blob_auth']));

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
                    $this->addUpload($form);
                }
            } else {
                if ($form->has('upload')) {
                    $form->remove('upload');
                }
                if (!$form->has('blob_auth')) {
                    $form->add('blob_auth', 'hidden', ['property_path' => 'blob.authcode']);
                }
            }
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\TicketAttachment $attachment */
        $attachment = $event->getData() instanceof FeedbackAttachment ? $event->getData() : new FeedbackAttachment();
        $form       = $event->getForm();
        $person     = $form->getConfig()->getOption('person');

        if ($form->has('upload')) {
            $file = $form->get('upload')->getData();

            if ($file instanceof File) {
                $error = $this->attachment_accepter->getError($file, 'user');
                if ($error) {
                    $error_code = $error['error_code'];
                    $params     = [];
                    if ($error_detail = $error['error_detail']) {
                        $params = ['detail' => $error_detail];
                    }
                    $phrase = sprintf('portal.forms.error_accept_%s', $error_code);
                    $form->get('upload')->addError(new FormError($phrase, $phrase, $params));

                    return;
                }
            } else {
                $error = [];
            }

            if ($file instanceof File && empty($error) && $file->getRealPath()) {
                $blob = $this->blob_storage->createBlobRecordFromFile(
                    $file->getRealPath(),
                    $file->getClientOriginalName(),
                    $file->getClientMimeType()
                );

                $attachment->setBlob($blob);
                $attachment->setPerson($person);

                $form->remove('upload');
                $form->add('delete', 'checkbox', ['mapped' => false, 'required' => false]);
                $form->add('blob_auth', 'hidden', ['property_path' => 'blob.authcode']);
            }
        } else {
            if (!$form->has('blob_auth')) {
                $form->add('blob_auth', 'hidden', ['property_path' => 'blob.authcode']);
            }
        }

        if ($attachment->getPerson() != $person) {
            $attachment->setPerson($person);
        }
        $form->setData($attachment);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'feedback_attachment';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'Application\\DeskPRO\\Entity\\FeedbackAttachment',
            ])
            ->setRequired('person')
            ->setAllowedTypes('person', 'Application\\DeskPRO\\Entity\\Person')
        ;
    }
}
