<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDataFeedback;
use Application\DeskPRO\Entity\CustomDataOrganization;
use Application\DeskPRO\Entity\CustomDataPerson;
use Application\DeskPRO\Entity\CustomDataTicket;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyNode;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class CustomDataType.
 */
class CustomDataType extends AbstractType
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager
     */
    protected $fieldManager;

    /**
     * Constructor.
     *
     * @param CustomFieldManager $fieldManager
     */
    public function __construct(CustomFieldManager $fieldManager)
    {
        $this->fieldManager = $fieldManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($form->all() as $child) {
            // set it to the first child's label
            if (!$view->vars['help']) {
                $option = $child->getConfig()->getOption('help');
                if ($option) {
                    $view->vars['help'] = $option;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onGenerateFields']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onTransformToCustomData'], -1);

        if ($options['inline']) {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetInlineData']);
        }
        if ($options['owner_form']) {
            $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onGenerateFields'], 100);
        }
    }

    /**
     * Generate form fields.
     *
     * @param FormEvent $event
     * @param string    $eventName
     */
    public function onGenerateFields(FormEvent $event, $eventName)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        /** @var CustomDefAbstract $customDef */
        $customDef = $config->getOption('custom_def');
        $field     = $this->fieldManager->createCustomField($customDef, $config->getOption('inline'));

        // custom fields are implemented as a compound type
        // and this label is for the 'data' attribute, whereas
        // the real label will be on the parent form which is adding the field
        $options = array_merge($field->getOptions(), [
            'label'          => false,
            'help'           => false,
            'error_bubbling' => true,
            'mapped'         => false,
        ]);

        if ($config->getOption('ignore_validation')) {
            $options = array_merge($options, [
                'validation_groups' => [],
                'constraints'       => null,
            ]);
        }

        // we need to track and re-generate the form if custom data owner has changed
        $viewData  = null;
        $ownerForm = $config->getOption('owner_form');
        if ($ownerForm && $eventName === FormEvents::SUBMIT) {
            $owner = $ownerForm->getData();
            $data  = $owner->custom_data;

            $viewData = $form->get('data')->getViewData();

            $form->setData($data);
            $form->remove('data');
        } else {
            $data = $event->getData();
        }

        // child field is not mapped so the form tries to get data from the options
        // so we should pass stored value via its options
        $options['data'] = $this->getFormData($data, $customDef);

        $form->add('data', $field->getType(), $options);

        if ($ownerForm && $eventName === FormEvents::SUBMIT) {
            $form->get('data')->submit($viewData);
        }
    }

    /**
     * Set form data from inline value.
     *
     * @param FormEvent $event
     */
    public function onSetInlineData(FormEvent $event)
    {
        $data = $event->getData();

        // default format based on form "data" field
        if (isset($data['data'])) {
            return;
        }

        // custom data serializer format we get from api response
        if (isset($data['value'])) {
            $data = $data['value'];
        }

        $event->setData([
            'data' => $data,
        ]);
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

        /** @var CustomDefAbstract $customDef */
        $customDef = $config->getOption('custom_def');

        /* @var CustomDataAbstract[]|ArrayCollection $allCustomData */
        $allCustomData = $form->getData() ?: new ArrayCollection();
        $customDefData = $this->filterCustomDefData($allCustomData, $customDef);

        if ($customDef->isChoiceType()) {
            $data = $form->get('data')->getNormData();
            $data = is_array($data) ? $data : ($data ? [$data] : []);
            $data = array_map(function (HierarchyNode $choiceCustomDef) {
                return $choiceCustomDef->getData()->getId();
            }, $data);

            $exist = $customDefData
                ->map(function (CustomDataAbstract $custom_data) {
                    return $custom_data->field->getId();
                })
                ->toArray()
            ;

            // remove deleted items
            foreach ($customDefData as $customData) {
                if (!in_array($customData->field->getId(), $data)) {
                    $customDefData->removeElement($customData);
                }
            }

            // add new items
            foreach ($data as $fieldId) {
                if (!in_array($fieldId, $exist)) {
                    $customData = $this->createCustomData($customDef);
                    $customData->setValue(1);
                    $customData->setField($customDef->getChildById($fieldId));

                    $customDefData->add($customData);
                }
            }
        } else {
            if ($customDefData->count()) {
                $customData = $customDefData->first();
                $customData->setData($form->get('data')->getData());
            } else {
                $customData = $this->createCustomData($customDef);
                $customData->setData($form->get('data')->getData());

                $customDefData->add($customData);
            }
        }

        // Set reference to custom def field.
        foreach ($customDefData as $customData) {
            $customData->root_field = $customDef;

            if ($customDef->getType() !== 'choice') {
                // for simple custom data field = root field
                $customData->field = $customDef;
            }
        }

        // Merge custom def data with existing owner custom data collection.
        foreach ($customDefData as $customData) {
            if (!$allCustomData->contains($customData)) {
                $allCustomData->add($customData);
            }
        }
        foreach ($allCustomData as $customData) {
            if ($customData->root_field === $customDef && !$customDefData->contains($customData)) {
                $allCustomData->removeElement($customData);
            }
        }

        $event->setData(clone $allCustomData);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'label' => function (Options $options) {
                    /** @var \Application\DeskPRO\Entity\CustomDefAbstract $field */
                    $field = $options['custom_def'];

                    return $field ? $field->getTitle() : '';
                },
                'help' => function (Options $options) {
                    /** @var \Application\DeskPRO\Entity\CustomDefAbstract $field */
                    $field = $options['custom_def'];

                    return $field ? $field->getDescription() : '';
                },
                'inline'            => false,
                'owner_form'        => false,
                'error_bubbling'    => false,
                'ignore_validation' => false,
                'fully_hidden'      => function (Options $options) {
                    /** @var \Application\DeskPRO\Entity\CustomDefAbstract $field */
                    $field = $options['custom_def'];

                    return $field && $field->getType() === CustomDefAbstract::TYPE_HIDDEN;
                },
                'constraints' => function (Options $options) {
                    return [
                        new AppAssert\CustomField\CustomData([
                            'context'    => $options['agent_interface'] ? 'agent' : 'user',
                            'custom_def' => $options['custom_def'],
                        ]),
                    ];
                },

            ])
            ->setRequired([
                'custom_def',
                'agent_interface',
            ])
            ->setAllowedTypes([
                'custom_def'      => CustomDefAbstract::class,
                'agent_interface' => 'bool',
                'inline'          => 'bool',
            ])
        ;
    }

    /**
     * @param ArrayCollection   $customData
     * @param CustomDefAbstract $customDef
     *
     * @return mixed
     */
    protected function getFormData($customData, CustomDefAbstract $customDef)
    {
        $allCustomData = $customData ?: new ArrayCollection();
        $customDefData = $this->filterCustomDefData($allCustomData, $customDef);

        $formFieldData = null;
        if ($customDefData->count()) {
            $formFieldData = $customDefData->first()->getData();
            if ($customDef->isChoiceType()) {
                $formFieldData = $customDefData
                    ->map(function (CustomDataAbstract $custom_data) {
                        return $custom_data->getFieldId();
                    })
                    ->toArray()
                ;

                $formFieldData = implode(',', $formFieldData);
            } elseif ($customDef->isDateType()) {
                // cast to null
                if (!$formFieldData) {
                    $formFieldData = null;
                }
            }
        }

        return $formFieldData;
    }

    /**
     * @param CustomDefAbstract $customDef
     *
     * @return CustomDataAbstract
     */
    protected function createCustomData(CustomDefAbstract $customDef)
    {
        if ($customDef instanceof CustomDefTicket) {
            return new CustomDataTicket();
        } elseif ($customDef instanceof CustomDefPerson) {
            return new CustomDataPerson();
        } elseif ($customDef instanceof CustomDefOrganization) {
            return new CustomDataOrganization();
        } elseif ($customDef instanceof CustomDefFeedback) {
            return new CustomDataFeedback();
        }

        throw new \RuntimeException('Unsupported custom data owner '.get_class($customDef));
    }

    /**
     * @param Collection        $allCustomData
     * @param CustomDefAbstract $customDef
     *
     * @return CustomDataAbstract[]|ArrayCollection
     */
    protected function filterCustomDefData(Collection $allCustomData, CustomDefAbstract $customDef)
    {
        $customDefData = $allCustomData->filter(function (CustomDataAbstract $custom_data) use ($customDef) {
            return $custom_data->root_field === $customDef;
        });

        if (!$customDefData->count()) {
            $defaultValue = $customDef->getDefaultValue();
            if ($defaultValue) {
                $defaultCustomData = $this->createCustomData($customDef);
                $defaultCustomData
                    ->setField($customDef)
                    ->setRootField($customDef)
                    ->setData($defaultValue)
                ;

                $customDefData->add($defaultCustomData);
            }
        }

        return $customDefData;
    }
}
