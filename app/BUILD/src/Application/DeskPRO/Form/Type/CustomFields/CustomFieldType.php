<?php

namespace Application\DeskPRO\Form\Type\CustomFields;

use Application\DeskPRO\CustomFields\CustomDataPersister;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\CustomFieldData;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class CustomFieldType extends AbstractType implements EventSubscriberInterface
{
    /**
     * @var \Application\DeskPRO\Entity\CustomFieldDefinition
     */
    protected $definition;

    public function __construct(CustomFieldDefinition $definition)
    {
        $this->definition = $definition;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addEventSubscriber($this);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'Application\DeskPRO\Entity\CustomFieldData',
                'label'      => $this->definition['title'],
                'attr'       => [
                    'data-definition-type' => $this->getName(),
                    'data-definition-id'   => $this->definition['id'],
                ],
                'allow_edit' => false,
            ])
            ->setRequired([
                'owner', 'persister',
            ])
            ->setDefined([
                'context', 'allow_edit',
            ])
            ->setAllowedTypes('owner', 'Application\DeskPRO\Domain\DomainObject')
            ->setAllowedTypes('persister', 'Application\DeskPRO\CustomFields\CustomDataPersister')
            ->setAllowedTypes('context', ['null', 'Application\DeskPRO\Domain\DomainObject'])
        ;
    }

    /**
     * @return array
     */
    protected function getValueOptions()
    {
        $options          = $this->definition['options'];
        $options['label'] = false;
        unset($options['allow_edit']);

        return $options;
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['def']           = $this->definition;
        $view->vars['rendered_data'] = null;

        if (!($data = $form->getData()) instanceof CustomFieldData) {
            return;
        }
        $view->vars['rendered_data'] = $data['input'];
    }

    /**
     * @return CustomFieldDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        // clean extra data
        if ($data = $event->getData()) {
            $data = array_intersect_key($data, $event->getForm()->all());
            $event->setData($data);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        if (!($data = $event->getData()) instanceof CustomFieldData) {
            return;
        }
        $form = $event->getForm();

        $options = $form->getConfig()->getOptions();
        /** @var CustomDataPersister $persister */
        $persister = $options['persister'];
        /** @var DomainObject $owner */
        $owner = $options['owner'];

        if ($data->getData()) {
            $persister->add($data);
            $data->owner           = $owner;
            $data->definition      = $this->definition;
            $data->root_definition = $this->definition;
        } else {
            $persister->remove($data);
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT  => 'onPreSubmit',
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }
}
