<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\EntityRepository\Blob as BlobRepo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Application\DeskPRO\Entity\TicketAttachment $attachment */
            $attachment = $event->getData() instanceof TicketAttachment ? $event->getData() : new TicketAttachment();
            $form = $event->getForm();

            if (!$attachment->getBlob()) {
                $this->addUpload($form);
            } else {
                $form
                    ->add('blob_auth', 'hidden', ['property_path' => 'blob.authcode'])
                    ->add('is_inline', 'checkbox', ['required' => false])
                    ->add('delete', 'checkbox', ['mapped' => false, 'required' => false])
                ;
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'postSubmit'], 600);
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
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form          = $event->getForm();
        $submittedData = $event->getData();
        if (array_key_exists('blob_auth', $submittedData)) {
            if (!$form->getData()) {
                $attachment = new TicketAttachment();
                $form->setData($attachment);
            }
            $form->getData()->setBlob($this->blob_repo->getByAuthCode($submittedData['blob_auth']));

            // delete?
            if (array_key_exists('delete', $submittedData)) {
                if ($submittedData['delete'] != 0) {
                    $attachment = $form->getData();
                    $blob       = $attachment->getBlob();

                    if ($blob) {
                        $this->blob_storage->deleteBlobRecord($blob);
                    }

                    $form->setData(null);
                    $form->remove('blob_auth');
                    $form->remove('is_inline');
                    $form->remove('delete');
                    $this->addUpload($form);
                }
            } else {
                if ($form->has('upload')) {
                    $form->remove('upload');
                }
                if (!$form->has('blob_auth')) {
                    $form
                        ->add('blob_auth', 'hidden', ['property_path' => 'blob.authcode'])
                        ->add('is_inline', 'checkbox', ['required' => false])
                    ;
                }
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\TicketAttachment $attachment */
        $attachment     = $event->getData() instanceof TicketAttachment ? $event->getData() : new TicketAttachment();
        $form           = $event->getForm();
        $person         = $form->getConfig()->getOption('person');
        $ticket_message = $form->getConfig()->getOption('ticket_message');

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
                $blob = $this->attachment_accepter->accept($file, true);

                $attachment->setBlob($blob);
                $attachment->setPerson($person);

                $ticket_message->addAttachment($attachment);

                $form->remove('upload');
                $form->add('delete', 'checkbox', ['mapped' => false, 'required' => false]);
                $form->add('blob_auth', 'hidden', ['property_path' => 'blob.authcode']);
                $form->add('is_inline', 'checkbox', ['required' => false]);
            } else {
                $ticket_message->attachments->removeElement($attachment);
            }
        } else {
            if (!$form->has('blob_auth')) {
                $form
                    ->add('blob_auth', 'hidden', ['property_path' => 'blob.authcode'])
                    ->add('is_inline', 'checkbox', ['required' => false])
                ;
                $ticket_message->addAttachment($attachment);
            }
        }

        if ($attachment->getPerson() !== $person) {
            $attachment->setPerson($person);
        }
        $ticket_message->addAttachment($attachment);
        $form->setData($attachment);
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
                'data_class' => 'Application\\DeskPRO\\Entity\\TicketAttachment',
            ])
            ->setRequired([
                'ticket_message',
                'person',
            ])
            ->setAllowedTypes([
                'ticket_message' => 'Application\\DeskPRO\\Entity\\TicketMessage',
                'person'         => 'Application\\DeskPRO\\Entity\\Person',
            ])
        ;
    }
}
