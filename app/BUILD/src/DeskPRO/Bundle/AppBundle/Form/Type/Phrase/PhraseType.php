<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Phrase;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Phrase;
use DeskPRO\Bundle\AppBundle\Entity\PhraseTranslatableInterface;
use DeskPRO\Bundle\AppBundle\Form\Type\HtmlTextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PhraseType.
 */
class PhraseType extends AbstractType
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
            ->add('value', HtmlTextareaType::class, [
                'property_path' => 'phrase',
            ])
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
                'data_class'     => Phrase::class,
                'error_bubbling' => false,
            ])
            ->setRequired([
                'prop_name',
                'owner',
            ])
            ->addAllowedTypes([
                'prop_name' => 'string',
                'owner'     => PhraseTranslatableInterface::class,
            ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetRelations(FormEvent $event)
    {
        $context = new PhraseContext($event);

        /* @var Phrase $data */
        $data = $event->getData();
        $data->setName($context->getPhraseName());
    }
}
