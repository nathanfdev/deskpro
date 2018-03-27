<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\ObjectLang;

use Application\DeskPRO\Entity\ObjectLang;
use DeskPRO\Bundle\AppBundle\Entity\ObjectTranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ObjectLangCollectionType.
 */
class ObjectLangCollectionType extends AbstractType
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
                'owner'     => ObjectTranslatableInterface::class,
            ])
            ->setDefaults([
                'mapped'         => false,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
                'entry_type'     => ObjectLangType::class,
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
        $context = new ObjectLangContext($event);
        $owner   = $context->getOwner();

        if ($owner->getId()) {
            $data = $owner->getObjectPropTranslations($context->getPropName());
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
        $context = new ObjectLangContext($event);
        $owner   = $context->getOwner();

        $allObjects  = $owner->getObjectPropsTranslations();
        $propObjects = $event->getData();

        /** @var ObjectLang $objectLang */
        foreach ($propObjects as $objectLang) {
            if (!$allObjects->contains($objectLang)) {
                $allObjects->add($objectLang);
            }
        }
        foreach ($allObjects as $objectLang) {
            if ($objectLang->getPropName() === $context->getPropName() && !$propObjects->contains($objectLang)) {
                $allObjects->removeElement($objectLang);
            }
        }

        $event->setData($allObjects);
    }
}
