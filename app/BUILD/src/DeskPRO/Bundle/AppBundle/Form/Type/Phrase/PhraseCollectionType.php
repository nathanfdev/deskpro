<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Phrase;

use Application\DeskPRO\Entity\Phrase;
use DeskPRO\Bundle\AppBundle\Entity\PhraseTranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PhraseCollectionType.
 */
class PhraseCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onLoadData']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onMergeData']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['prop_name', 'owner'])
            ->addAllowedTypes([
                'prop_name' => 'string',
                'owner'     => PhraseTranslatableInterface::class,
            ])
            ->setDefaults([
                'mapped'         => false,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
                'entry_type'     => PhraseType::class,
                'entry_options'  => function (Options $options) {
                    return [
                        'prop_name' => $options['prop_name'],
                        'owner'     => $options['owner'],
                    ];
                },
            ])
        ;
    }

    /**
     * Load related entity property translations.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onLoadData(FormEvent $event)
    {
        $context = new PhraseContext($event);
        $owner   = $context->getOwner();

        if ($owner->getId()) {
            $data = $owner->getPhrasePropTranslations($context->getPropName());
        } else {
            $data = new ArrayCollection();
        }

        $event->setData($data);
    }

    /**
     * Set updated translations.
     * Will be saved after entity persist via object translatable lifecycle callback.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onMergeData(FormEvent $event)
    {
        $context = new PhraseContext($event);
        $owner   = $context->getOwner();

        $allObjects  = $owner->getPhraseTranslations();
        $propObjects = $event->getData();

        /** @var Phrase $phrase */
        foreach ($propObjects as $phrase) {
            if (!$allObjects->contains($phrase)) {
                $allObjects->add($phrase);
            }
        }
        foreach ($allObjects as $phrase) {
            if ($phrase->getName() === $context->getPhraseName() && !$propObjects->contains($phrase)) {
                $allObjects->removeElement($phrase);
            }
        }

        $event->setData($allObjects);
    }
}
