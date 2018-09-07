<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\ExternalEvent;

use DeskPRO\Bundle\AppBundle\Form\Type\JsonArrayType;
use DeskPRO\Bundle\AppBundle\Model\PopupModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PopupTargetType.
 */
class PopupTargetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'required'          => true,
                'choices_as_values' => true,
                'choices'           => [
                    PopupModel::TARGET_TYPE_LIST,
                    PopupModel::TARGET_TYPE_QUERY,
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'finishForm']);
    }

    /**
     * @param FormEvent $event
     */
    public function finishForm(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (isset($data['type']) && $data['type'] === PopupModel::TARGET_TYPE_LIST) {
            // for now conditions to find target and additional info just arrays
            $form->add('list', JsonArrayType::class, ['required' => true]);
        } elseif (isset($data['type'])) {
            $form
                // for now conditions to find target and additional info just arrays
                ->add('query', CollectionType::class, [
                    'entry_type'     => PopupQueryType::class,
                    'allow_add'      => true,
                    'allow_delete'   => true,
                    'error_bubbling' => false,
                    'required'       => true,
                    'constraints'    => [
                        new Assert\NotBlank(),
                    ],
                ])
            ;
        }
    }
}
