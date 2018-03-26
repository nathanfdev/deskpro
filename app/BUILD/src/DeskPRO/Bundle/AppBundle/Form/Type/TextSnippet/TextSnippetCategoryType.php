<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\TextSnippet;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TextSnippetCategory;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\ObjectLang\ObjectLangCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TextSnippetCategoryType.
 */
class TextSnippetCategoryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', ObjectLangCollectionType::class, [
                'mapped'    => false,
                'prop_name' => 'title',
                'owner'     => $builder->getData(),
                'required'  => true,
            ])
            ->add('is_global', ApiBooleanType::class, [
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetTypeAndPerson']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'    => TextSnippetCategory::class,
                'error_mapping' => [
                    'props_translations' => 'title',
                ],
            ])
            ->setRequired(['type', 'person'])
            ->setAllowedTypes('person', Person::class)
            ->setAllowedValues('type', [
                TextSnippetCategory::TYPE_TICKET,
                TextSnippetCategory::TYPE_CHAT,
            ])
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetTypeAndPerson(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $event->getForm()->getConfig();

        $person = $config->getOption('person');
        $type   = $config->getOption('type');

        /** @var TextSnippetCategory $data */
        $data = $form->getData();
        $data
            ->setTypename($type)
            ->setPerson($person)
        ;
    }
}
