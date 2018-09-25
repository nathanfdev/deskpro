<?php

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

        if ($options['with_tag']) {
            $builder->add('tag', ChoiceType::class, [
                'mapped'            => false,
                'required'          => false,
                'label'             => false,
                'choices_as_values' => true,
                'choices'           => ['', 'ticket_attachment'],
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
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
                'with_tag'     => false,
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

        $options = $form->getConfig()->getOptions();
        $file    = isset($data[$options['field_name']]) ? $data[$options['field_name']] : null;

        if ($file instanceof UploadedFile) {
            $fileForm = $form->get($options['field_name']);
            $error    = $this->acceptAttachment->getError($file, $options['upload_context']);
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
                $props = [];
                if ($options['with_tag'] && isset($data['tag'])) {
                    $this->fillTagProps($props, $data['tag']);
                }
                $form->setData($this->acceptAttachment->accept($file, true, $props));
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

    /**
     * @param array  $props
     * @param string $tagFormData `tag` form field value
     */
    protected function fillTagProps(&$props, $tagFormData)
    {
        if ($tagFormData === 'ticket_attachment') {
            $props['tag'] = 'ticket_attachment';
        }
    }
}
