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

use Application\DeskPRO\Attachments\AcceptAttachment;
use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Blob;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WebAcceptAttachmentType.
 */
class WebAcceptAttachmentType extends AbstractType
{
    /**
     * @var \Application\DeskPRO\BlobStorage\DeskproBlobStorage
     */
    private $blobStorage;

    /**
     * @var AcceptAttachment
     */
    private $acceptAttachment;

    /**
     * Constructor.
     *
     * @param DeskproBlobStorage $blobStorage
     * @param AcceptAttachment   $acceptAttachment
     */
    public function __construct(DeskproBlobStorage $blobStorage, AcceptAttachment $acceptAttachment)
    {
        $this->blobStorage      = $blobStorage;
        $this->acceptAttachment = $acceptAttachment;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('upload', FileType::class, [
            'mapped'   => false,
            'required' => false,
            'label'    => false,
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Blob::class,
        ]);
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
        $file = isset($data['upload']) ? $data['upload'] : null;

        if ($file instanceof UploadedFile) {
            $error = $this->acceptAttachment->getError($file, 'user');

            // Unable to accept, generate error
            if ($error) {
                $errorCode   = $error['error_code'];
                $errorDetail = $error['error_detail'];

                $params = [];
                if ($errorDetail) {
                    $params['detail'] = $errorDetail;
                }

                $phrase = sprintf('portal.forms.error_accept_%s', $errorCode);
                $form->get('upload')->addError(new FormError($phrase, $phrase, $params));
            } else {
                $form->setData($this->acceptAttachment->accept($file, true));
            }
        }
    }
}
