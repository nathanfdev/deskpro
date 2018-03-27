<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\ObjectLang;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\ObjectLang;
use DeskPRO\Bundle\AppBundle\Entity\ObjectTranslatableInterface;
use DeskPRO\Bundle\AppBundle\Form\Type\HtmlTextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ObjectLangType.
 */
class ObjectLangType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('language', EntityType::class, [
                'class' => Language::class,
            ])
            ->add('value', HtmlTextareaType::class)
        ;

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSetRelations']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'     => ObjectLang::class,
                'error_bubbling' => false,
            ])
            ->setRequired([
                'prop_name',
                'owner',
            ])
            ->addAllowedTypes([
                'prop_name' => 'string',
                'owner'     => ObjectTranslatableInterface::class,
            ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetRelations(FormEvent $event)
    {
        $context = new ObjectLangContext($event);

        /* @var ObjectLang $data */
        $data = $event->getData();
        $data
            ->setObject($context->getOwner())
            ->setPropName($context->getPropName())
        ;
    }
}
