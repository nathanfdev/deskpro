<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Form\DataTransformer\BlobAuthTransformer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BlobAuthType.
 */
class BlobAuthType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new BlobAuthTransformer($this->em));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetInline']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compound' => false,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetInline(FormEvent $event)
    {
        $data = $event->getData();
        if (is_array($data)) {
            $event->setData(isset($data['blob_auth']) ? $data['blob_auth'] : null);
        }
    }
}
