<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AcceptAttachmentType.
 */
class AcceptAttachmentType extends AbstractType
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
        $constraints = [new Assert\File()];
        if ($options['required']) {
            $constraints[] = new Assert\NotNull();
        }

        $builder->add($options['field_name'], FileType::class, [
            'mapped'      => false,
            'required'    => false,
            'label'       => false,
            'constraints' => $constraints,
        ]);

        if ($options['with_context']) {
            $builder->add('context', ChoiceType::class, [
                'mapped'            => false,
                'required'          => false,
                'label'             => false,
                'choices_as_values' => true,
                'choices'           => ['', 'image'],
            ]);

            $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onValidateContext']);
        }

        $builder->get($options['field_name'])->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'   => Blob::class,
                'field_name'   => 'file',
                'required'     => false,
                'with_context' => false,
            ])
            ->setRequired(['upload_context'])
            ->setAllowedValues('upload_context', ['agent', 'user'])
            ->setAllowedTypes('with_context', 'bool')
        ;
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

        if ($data instanceof UploadedFile) {
            $parentForm = $form->getParent();
            $options    = $parentForm->getConfig()->getOptions();

            $error = $this->acceptAttachment->getError($data, $options['upload_context']);
            if ($error) {
                // unable to accept the file, add an error
                $errorCode   = 'accept_'.$error['error_code'];
                $errorDetail = $error['error_detail'];

                $params = [];
                if ($errorDetail) {
                    $params['detail'] = $errorDetail;
                }

                $form->addError(new FormError($errorCode, $errorCode, $params));
            } else {
                // set blob data
                $parentForm->setData($this->acceptAttachment->accept($data, true));
            }
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onValidateContext(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (!$data instanceof Blob) {
            return;
        }

        $options = $form->getConfig()->getOptions();
        $context = $form->get('context')->getData();
        if ($context === 'image' && !$data->isImage()) {
            $form->get($options['field_name'])->addError(new FormError(ErrorsCodes::NOT_AN_IMAGE));
        }
    }
}
