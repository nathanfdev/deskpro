<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Attachments;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\Blob as BlobRepository;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AttachmentType.
 */
class BaseAttachmentType extends AbstractType
{
    /**
     * @var BlobRepository
     */
    private $blobRepo;

    /**
     * Constructor.
     *
     * @param BlobRepository $blobRepo
     */
    public function __construct(BlobRepository $blobRepo)
    {
        $this->blobRepo = $blobRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreData'], 100);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'], 100);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'error_bubbling' => false,
                'constraints'    => [
                    // check attachments directly via the form to prevent checking all collection
                    new Assert\Valid(),
                ],
            ])
            ->setRequired(['data_class', 'person'])
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreData(FormEvent $event)
    {
        $form    = $event->getForm();
        $options = $form->getConfig()->getOptions();
        $data    = $event->getData();

        // set initial value
        if (!$data) {
            $data = new $options['data_class']();
        }

        $event->setData($data);
        if ($data->getBlob()) {
            $this->renderAuthCode($form);
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

        if (!is_array($data)) {
            return;
        }

        // trying to assign a real blob to attachment
        if (array_key_exists('blob_auth', $data)) {
            // find for existing blob by auth code
            if (is_scalar($data['blob_auth']) && $data['blob_auth']) {
                $blob = $this->blobRepo->getByAuthCode($data['blob_auth']);
            } else {
                $blob = null;
            }

            // or just set empty blob to correct setting of form fields and form validation
            if (!$blob) {
                $blob = new Blob();
                $blob->setAuthCode('');
            }

            $attachment = $form->getData();
            $attachment->setBlob($blob);

            $this->renderAuthCode($form);
        }
    }

    /**
     * Deletes the attachment record if it does not contain a valid blob after all processing is done.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (!$data) {
            return;
        }

        if ($data->getBlob()) {
            $this->renderAuthCode($form);
        } else {
            $event->setData(null);
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form       = $event->getForm();
        $attachment = $event->getData();
        $person     = $form->getConfig()->getOption('person');

        if (!is_object($attachment)) {
            return;
        }

        $blob = $attachment->getBlob();
        if ($blob instanceof Blob && $blob->getId()) {
            $blob->setIsTemp(false);

            if ($attachment->getPerson() !== $person) {
                $attachment->setPerson($person);
            }
        } else {
            $form->addError(new FormError(ErrorsCodes::NO_UPLOADED_FILE));
        }
    }

    /**
     * @param FormInterface $form
     */
    private function renderAuthCode(FormInterface $form)
    {
        if ($form->has('blob_auth')) {
            return;
        }

        $form->add('blob_auth', HiddenType::class, [
            'property_path'  => 'blob.authcode',
            'error_bubbling' => false,
        ]);
    }
}
