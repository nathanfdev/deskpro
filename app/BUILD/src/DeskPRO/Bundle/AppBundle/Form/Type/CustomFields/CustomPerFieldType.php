<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\CustomFields;

use Application\DeskPRO\Entity\CustomFieldData;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Component\Hierarchy\HierarchyNode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CustomPerFieldType.
 */
class CustomPerFieldType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onGenerateFields']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onTransformToCustomData'], -1);

        if ($options['inline']) {
            $builder->addEventSubscriber(new InlineCustomDataListener());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['custom_def'] = $options['custom_def'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired([
                'agent_interface',
                'inline',
                'custom_def',
                'owner',
            ])
            ->setAllowedTypes('agent_interface', 'bool')
            ->setAllowedTypes('inline', 'bool')
            ->setAllowedTypes('custom_def', CustomFieldDefinition::class)
            ->setAllowedTypes('owner', [Person::class, Organization::class])
        ;
    }

    /**
     * Generate form fields.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onGenerateFields(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        /** @var CustomFieldDefinition $customDef */
        $customDef = $config->getOption('custom_def');
        $required  = $customDef->isRequired($config->getOption('agent_interface'));

        $constraints = [];
        if ($required) {
            $constraints[] = new Assert\NotBlank();
        }

        $options = [
            'required'           => $required,
            'expanded'           => $customDef->isExpanded(),
            'multiple'           => $customDef->isMultiple(),
            'custom_field'       => $customDef,
            'label'              => false,
            'constraints'        => $constraints,
            'help'               => $customDef->getDescription(),
            'contextual_choices' => $customDef->getChoices($config->getOption('owner'))->toArray(),
            'mapped'             => false,

            // child field is not mapped so the form tries to get data from the options
            // so we should pass stored value via its options
            'data' => $this->getFormData($event->getData() ?: new ArrayCollection(), $customDef),
        ];

        $form->add('data', CustomPerFieldChoiceType::class, $options);
    }

    /**
     * Transforms form data to modified custom data collection.
     *
     * @param FormEvent $event
     */
    public function onTransformToCustomData(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        /** @var CustomFieldDefinition $customDef */
        $customDef = $config->getOption('custom_def');

        /* @var CustomFieldData[]|ArrayCollection $allCustomData */
        $allCustomData = $form->getData() ?: new ArrayCollection();
        $customDefData = $this->filterCustomDefData($allCustomData, $customDef);

        $data = $form->get('data')->getNormData();
        $data = is_array($data) ? $data : ($data ? [$data] : []);
        $data = array_map(function (HierarchyNode $choiceCustomDef) {
            return $choiceCustomDef->getData()->getId();
        }, $data);

        $exist = $customDefData
            ->map(function (CustomFieldData $customData) {
                return $customData->getDefinition()->getId();
            })
            ->toArray()
        ;

        // remove deleted items
        foreach ($customDefData as $customData) {
            if (!in_array($customData->getDefinition()->getId(), $data)) {
                $customDefData->removeElement($customData);
            }
        }

        // add new items
        foreach ($data as $fieldId) {
            if (!in_array($fieldId, $exist)) {
                $customData = new CustomFieldData();
                $customData
                    ->setRootDefinition($customDef)
                    ->setDefinition($customDef->getChildById($fieldId))
                    ->setData(1)
                ;

                $customDefData->add($customData);
            }
        }

        // Merge custom def data with existing owner custom data collection.
        foreach ($customDefData as $customData) {
            if (!$allCustomData->contains($customData)) {
                $allCustomData->add($customData);
            }
        }
        foreach ($allCustomData as $customData) {
            if ($customData->getRootDefinition() === $customDef && !$customDefData->contains($customData)) {
                $allCustomData->removeElement($customData);
            }
        }

        $event->setData(clone $allCustomData);
    }

    /**
     * Prepare form data from custom def data collection.
     *
     * @param ArrayCollection       $customData
     * @param CustomFieldDefinition $customDef
     *
     * @return mixed
     */
    protected function getFormData($customData, CustomFieldDefinition $customDef)
    {
        $allCustomData = $customData ?: new ArrayCollection();
        $customDefData = $this->filterCustomDefData($allCustomData, $customDef);

        $formFieldData = null;
        if ($customDefData->count()) {
            $formFieldData = $customDefData
                ->map(function (CustomFieldData $custom_data) {
                    return $custom_data->getDefinition()->getId();
                })
                ->toArray()
            ;

            if (!$customDef->isMultiple()) {
                $formFieldData = reset($formFieldData);
            }
        }

        return $formFieldData;
    }

    /**
     * Filter custom def data from custom data collection.
     *
     * @param Collection            $allCustomData
     * @param CustomFieldDefinition $customDef
     *
     * @return CustomFieldData[]|ArrayCollection
     */
    protected function filterCustomDefData(Collection $allCustomData, CustomFieldDefinition $customDef)
    {
        $customDefData = $allCustomData->filter(function (CustomFieldData $customData) use ($customDef) {
            return $customData->getRootDefinition() === $customDef && null !== $customData->getDefinition();
        });

        return $customDefData;
    }
}
