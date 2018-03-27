<?php

namespace DeskPRO\Bundle\AppBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class ChoiceExtension.
 */
class ChoiceExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['expanded']) {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onUnsetExpandedChoices'], -1);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ChoiceType::class;
    }

    /**
     * We use PATCH request for all updates so it does not allow to deselect values.
     * It's because they are not submitted in the PATCH request. So submit them as well.
     *
     * @param FormEvent $event
     */
    public function onUnsetExpandedChoices(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        foreach ($form->all() as $name => $child) {
            if (!array_key_exists($name, $data)) {
                $data[$name] = false;
            }
        }

        $event->setData($data);
    }
}
