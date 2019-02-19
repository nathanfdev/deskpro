<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\CustomFields;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\Currency;
use DeskPRO\Bundle\AppBundle\Form\FormField;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\DataJsonType;
use DeskPRO\Bundle\AppBundle\Form\Type\DataListType;
use DeskPRO\Bundle\AppBundle\Form\Type\DateTimeType;
use DeskPRO\Bundle\AppBundle\Form\Type\DisplayHtmlType;
use DeskPRO\Bundle\AppBundle\Form\Type\DpDateType;
use DeskPRO\Bundle\AppBundle\Form\Type\DpHiddenType;
use DeskPRO\Bundle\AppBundle\Form\Type\DpUrlType;
use DeskPRO\Bundle\AppBundle\Form\Type\MoneyType;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\SingleCheckboxType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CustomDataType.
 */
class CustomDataType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param ValidatorInterface $validator
     */
    public function __construct(EntityManager $em, ValidatorInterface $validator)
    {
        $this->em        = $em;
        $this->validator = $validator;
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit'], -1);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], -1);
        $builder->addEventSubscriber(new InlineCustomDataListener());
    }

    /**
     * Generate form fields.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        /** @var CustomDefAbstract $customDef */
        $customDef = $config->getOption('custom_def');
        $field     = $this->createCustomField(
            $customDef,
            $config->getOption('inline'),
            $config->getOption('agent_interface')
        );

        if (!$field) {
            return;
        }

        // custom fields are implemented as a compound type
        // and this label is for the 'data' attribute, whereas
        // the real label will be on the parent form which is adding the field
        $options = array_merge($field->getOptions(), [
            'label'          => false,
            'help'           => false,
            'error_bubbling' => true,
            'mapped'         => false,
        ]);

        // child field is not mapped so the form tries to get data from the options
        // so we should pass stored value via its options
        $options['data'] = $this->getFormData($event->getData() ?: new ArrayCollection(), $customDef);

        $form->add('data', $field->getType(), $options);
    }

    /**
     * Transforms form data to modified custom data collection.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        /** @var CustomDefAbstract $customDef */
        $customDef = $config->getOption('custom_def');

        /* @var CustomDataAbstract[]|ArrayCollection $allCustomData */
        $allCustomData = $form->getData() ?: new ArrayCollection();

        /** @var CustomDataAbstract[] */
        $customDefData = $this->filterCustomDefData($allCustomData, $customDef);

        if ($customDef->isChoiceType()) {
            $data = $form->get('data')->getData();
            $data = is_array($data) ? $data : ($data ? [$data] : []);
            $data = array_map(function (CustomDefAbstract $choiceCustomDef) {
                return $choiceCustomDef->getId();
            }, $data);

            $exist = $customDefData
                ->map(function (CustomDataAbstract $custom_data) {
                    return $custom_data->getField()->getId();
                })
                ->toArray()
            ;

            // remove deleted items
            foreach ($customDefData as $customData) {
                if (!in_array($customData->getField()->getId(), $data)) {
                    $customDefData->removeElement($customData);
                }
            }

            // add new items
            foreach ($data as $blobId) {
                if (!in_array($blobId, $exist)) {
                    if (!$blobId || !$choiceDef = $customDef->getChildById($blobId)) {
                        continue;
                    }

                    $customData = $customDef->createCustomData();
                    $customData->setValue(1);
                    $customData->setField($choiceDef);

                    $customDefData->add($customData);
                }
            }
        } elseif ($customDef->isDataListType()) {
            $data     = $form->get('data')->getData();
            $itemList = is_string($data) ? json_decode($data) : [];

            // remove deleted items and collect the id's of existing ones
            $existingItemList = [];
            foreach ($customDefData as $customData) {
                if (!in_array($customData->getData(), $itemList)) {
                    $customDefData->removeElement($customData);
                } else {
                    array_push($existingItemList, $customData->getData());
                }
            }

            $newItemList = array_diff($itemList, $existingItemList);
            foreach ($newItemList as $item) {
                $customData = $customDef->createCustomData();
                $customData->setData($item);
                $customDefData->add($customData);
            }
        } elseif ($customDef->isFileType()) {
            $data = $form->get('data')->getData();
            $data = is_array($data) ? $data : ($data ? [$data] : []);

            $newBlobIds = [];
            foreach ($data as $blob) {
                if ($blob instanceof Blob && $blob->getId()) {
                    $newBlobIds[] = $blob->getId();
                } else {
                    // set null for validation
                    $newBlobIds[] = null;
                }
            }
            $existBlobIds = $customDefData
                ->map(function (CustomDataAbstract $custom_data) {
                    return $custom_data->getValue();
                })
                ->toArray()
            ;

            // remove deleted items
            foreach ($customDefData as $customData) {
                if (!in_array($customData->getValue(), $newBlobIds)) {
                    $customDefData->removeElement($customData);
                }
            }

            // add new items
            foreach ($newBlobIds as $blobId) {
                if (!in_array($blobId, $existBlobIds)) {
                    $customData = $customDef->createCustomData();
                    $customData->setValue($blobId);

                    $customDefData->add($customData);
                }
            }
        } else {
            $data = $form->get('data')->getData();

            if ($customDefData->count()) {
                $customData = $customDefData->first();
                $customData->setData($data);
            } else {
                $customData = $customDef->createCustomData();
                $customData->setData($data);

                $customDefData->add($customData);
            }
        }

        // Set reference to custom def field.
        foreach ($customDefData as $customData) {
            $customData->setRootField($customDef);

            if (!$customDef->isChoiceType()) {
                // for simple custom data field = root field
                $customData->setField($customDef);
            }
        }

        // Merge custom def data with existing owner custom data collection.
        foreach ($customDefData as $customData) {
            if (!$allCustomData->contains($customData)) {
                $allCustomData->add($customData);
            }
        }
        foreach ($allCustomData as $customData) {
            if ($customData->getRootField() === $customDef && !$customDefData->contains($customData)) {
                $allCustomData->removeElement($customData);
            }
        }

        $event->setData(clone $allCustomData);
    }

    /**
     * We need to map errors to the custom data form.
     *
     * Because we have single custom data collection for all custom def fields we need to get validation errors from
     * unmapped field. So validate the data manually via another validator to keep custom data mapped.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form    = $event->getForm();
        $options = $form->getConfig()->getOptions();

        if (!$form->isSubmitted()) {
            return;
        }
        if ($form->get('data') && $form->get('data')->getTransformationFailure()) {
            // don't validate if we've already got an error on data transformation
            return;
        }

        /** @var CustomDefAbstract $customDef */
        $customDef = $options['custom_def'];
        $context   = $options['agent_interface'] ? 'agent' : 'user';

        if ($context === 'agent' && $customDef->getOption('agent_validation_resolve')) {
            $ticket = $options['ticket'];
            if ($ticket instanceof Ticket && !$ticket->isResolved()) {
                return;
            }
        }

        $violations = $this->validator->validate($form->getData(), new AppAssert\CustomField\CustomData([
            'context'        => $context,
            'custom_def'     => $customDef,
            'target'         => AppAssert\CustomField\CustomData::TARGET_FIELD,
            'check_required' => $options['check_required'],
        ]));

        foreach ($violations as $violation) {
            $form->addError(new FormError(
                $violation->getMessage(),
                $violation->getMessageTemplate(),
                $violation->getParameters(),
                $violation->getPlural(),
                $violation
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label' => function (Options $options) {
                    $noLabelTypes = [CustomDefAbstract::TYPE_HIDDEN, CustomDefAbstract::TYPE_DISPLAY];
                    if (in_array($options['custom_def']->getType(), $noLabelTypes)) {
                        return false;
                    }

                    return $options['custom_def']->getTitle();
                },
                'help' => function (Options $options) {
                    return $options['custom_def']->getDescription();
                },
                'fully_hidden' => function (Options $options) {
                    return $options['custom_def']->getType() === CustomDefAbstract::TYPE_HIDDEN;
                },
                'required' => function (Options $options) {
                    return $options['custom_def']->isRequired($options['agent_interface'])
                        || $options['custom_def']->isRegexRequired($options['agent_interface']);
                },
                'inline'         => false,
                'error_bubbling' => false,
                'ticket'         => false,
                'check_required' => true,
            ])
            ->setRequired([
                'custom_def',
                'agent_interface',
            ])
            ->setAllowedTypes('custom_def', CustomDefAbstract::class)
            ->setAllowedTypes('agent_interface', 'bool')
            ->setAllowedTypes('inline', 'bool')
            ->setAllowedTypes('check_required', 'bool')
            ->setAllowedTypes('ticket', ['bool', Ticket::class])
        ;
    }

    /**
     * Prepare form data from custom def data collection.
     *
     * @param ArrayCollection   $customData
     * @param CustomDefAbstract $customDef
     *
     * @return mixed
     */
    private function getFormData($customData, CustomDefAbstract $customDef)
    {
        $allCustomData = $customData ?: new ArrayCollection();
        $customDefData = $this->filterCustomDefData($allCustomData, $customDef);

        if ($customDef->isDataListType()) {
            $formFieldData = [];
            foreach ($customDefData as $data) {
                array_push($formFieldData, $data->getData());
            }

            return $formFieldData;
        }

        $formFieldData = null;
        if ($customDefData->count()) {
            $formFieldData = $customDefData->first()->getData();
            if ($customDef->isChoiceType()) {
                $formFieldData = $customDefData
                    ->map(function (CustomDataAbstract $customData) {
                        return $customData->getFieldId();
                    })
                    ->toArray()
                ;

                if (!$customDef->isMulti()) {
                    $formFieldData = reset($formFieldData);
                }
            } elseif ($customDef->isDateType()) {
                // cast to null
                if (!$formFieldData) {
                    $formFieldData = null;
                }
            } elseif ($customDef->isFileType()) {
                $formFieldData = $customDefData
                    ->map(function (CustomDataAbstract $customData) {
                        return $this->em->getRepository(Blob::class)->find($customData->getValue());
                    })
                    ->toArray()
                ;
            }
        }

        return $formFieldData;
    }

    /**
     * Filter custom def data from custom data collection.
     *
     * @param Collection        $allCustomData
     * @param CustomDefAbstract $customDef
     *
     * @return CustomDataAbstract[]|ArrayCollection|Collection
     */
    private function filterCustomDefData(Collection $allCustomData, CustomDefAbstract $customDef)
    {
        $defaultValue  = $this->getDefaultValue($customDef);
        $customDefData = $allCustomData->filter(function (CustomDataAbstract $customData) use ($customDef) {
            return $customData->getRootField() === $customDef && null !== $customData->getField();
        });

        if (!$customDefData->count()) {
            if (!$customDef->isChoiceType()) {
                if ($defaultValue) {
                    $defaultCustomData = $customDef->createCustomData();
                    $defaultCustomData
                        ->setField($customDef)
                        ->setRootField($customDef)
                        ->setData($defaultValue)
                    ;

                    $customDefData->add($defaultCustomData);
                }
            } else {
                foreach ($defaultValue as $defaultId) {
                    $choiceDef = $customDef->getChildById($defaultId);
                    if (!$choiceDef) {
                        continue;
                    }

                    $defaultCustomData = $customDef->createCustomData();
                    $defaultCustomData
                        ->setField($choiceDef)
                        ->setRootField($customDef)
                        ->setValue(1)
                    ;

                    $customDefData->add($defaultCustomData);
                }
            }
        } else {
            // make sure we have no dupes
            if (!$customDef->isMulti()) {
                $customDefData = new ArrayCollection([$customDefData->first()]);
            }
        }

        return $customDefData;
    }

    /**
     * @param CustomDefAbstract $customDef
     *
     * @return mixed
     */
    private function getDefaultValue(CustomDefAbstract $customDef)
    {
        if (!$customDef->isChoiceType()) {
            $defaultValue = $customDef->getDefaultValue();

            // datetime default value stored as string, convert to timestamp
            if ($customDef->isDateType()) {
                if ($defaultValue) {
                    try {
                        $defaultValue = (new \DateTime($defaultValue))->getTimestamp();
                    } catch (\Exception $e) {
                        $defaultValue = null;
                    }
                }
            }
        } else {
            $defaultValue = (array) $customDef->getDefaultValue();
        }

        return $defaultValue;
    }

    /**
     * @param CustomDefAbstract $def
     * @param bool              $isInline
     * @param bool              $isAgentContext
     *
     * @throws \Exception
     *
     * @return FormField|null
     */
    private function createCustomField(CustomDefAbstract $def, $isInline = false, $isAgentContext = false)
    {
        switch ($def->getType()) {
            case CustomDefAbstract::TYPE_DATA_LIST:
                return new FormField(DataListType::class, [
                    'help' => $def->getRealDescription(),
                ]);
            case CustomDefAbstract::TYPE_DATA_JSON:
                return new FormField(DataJsonType::class, [
                    'help' => $def->getRealDescription(),
                ]);
            case CustomDefAbstract::TYPE_DATA:
            case CustomDefAbstract::TYPE_TEXT:
                return new FormField(TextType::class, [
                    'help' => $def->getRealDescription(),
                ]);

            case CustomDefAbstract::TYPE_TEXTAREA:
                return new FormField(TextareaType::class, [
                    'help' => $def->getRealDescription(),
                ]);

            case CustomDefAbstract::TYPE_TOGGLE:
                if ($isInline) {
                    return new FormField(ApiBooleanType::class);
                }

                $options = [
                    'checkbox_label' => $def->getOption('label_text') ?: '',
                    'force_boolean'  => true,
                    'help'           => $def->getRealDescription(),
                ];

                return new FormField(SingleCheckboxType::class, $options);

            case CustomDefAbstract::TYPE_DISPLAY:
                $options = [
                    'html'  => $def->getOption('html'),
                    'data'  => '',
                    'label' => false,
                    'help'  => $def->getRealDescription(),
                ];

                return new FormField(DisplayHtmlType::class, $options);

            case CustomDefAbstract::TYPE_CHOICE:
                $options = [
                    'expanded'     => (bool) $def->getOption('expanded'),
                    'multiple'     => (bool) $def->getOption('multiple'),
                    'custom_field' => $def,
                    'help'         => $def->getRealDescription(),
                ];

                return new FormField(CustomFieldChoiceType::class, $options);

            case CustomDefAbstract::TYPE_DATE:
                if ($isInline) {
                    $options = [
                        'input'  => 'timestamp',
                        'widget' => 'single_text',
                    ];
                } else {
                    $options = [
                        'input'    => 'timestamp',
                        'widget'   => 'choice',
                        'calendar' => $def->getOption('calendar'),
                        'weekdays' => $def->getOption('date_valid_dow'),
                        'min_date' => $def->getDateMinFormat(),
                        'max_date' => $def->getDateMaxFormat(),
                        'help'     => $def->getRealDescription(),
                    ];
                }

                return new FormField(DpDateType::class, $options);

            case CustomDefAbstract::TYPE_DATETIME:
                if ($isInline) {
                    $options = [
                        'input'  => 'timestamp',
                        'widget' => 'single_text',
                    ];

                    return new FormField(DateTimeType::class, $options);
                } else {
                    $options = [
                        'input'    => 'timestamp',
                        'widget'   => 'choice',
                        'format'   => 'Y-m-d H:i',
                        'weekdays' => $def->getOption('date_valid_dow'),
                        'min_date' => $def->getDateMinFormat(),
                        'max_date' => $def->getDateMaxFormat(),
                        'help'     => $def->getRealDescription(),
                    ];

                    return new FormField(DateTimeType::class, $options);
                }

            case CustomDefAbstract::TYPE_HIDDEN:
                $options = [
                    'auto_fill'          => false,
                    'hidden'             => true,
                    'label'              => false,
                    'help'               => false,
                    'cookie_param_name'  => $def->getOption('cookie_name'),
                    'request_param_name' => $def->getOption('param_name'),
                ];

                return new FormField(DpHiddenType::class, $options);

            case CustomDefAbstract::TYPE_URL:
                return new FormField(DpUrlType::class, [
                    'help' => $def->getRealDescription(),
                ]);

            case CustomDefAbstract::TYPE_CURRENCY:
                if (!$def->getOption('currency_id')) {
                    // unable to get the field's currency, skipping
                    return;
                }

                $currency = $this->em->getRepository(Currency::class)->find($def->getOption('currency_id'));
                if (!$currency) {
                    // unable to get the field's currency, skipping
                    return;
                }

                return new FormField(MoneyType::class, [
                    'help'     => $def->getRealDescription(),
                    'currency' => $currency->getCurrencyCode(),
                    'divisor'  => $currency->getDelimiter(),
                    'grouping' => true,
                ]);

            case CustomDefAbstract::TYPE_FILE:
                return new FormField(CustomFieldFileCollectionType::class, [
                    'custom_field'    => $def,
                    'agent_interface' => $isAgentContext,
                ]);

            default:
                throw new \InvalidArgumentException("Invalid field #{$def->getId()}. Cannot find handler for type \"{$def->getType()}\".");
        }
    }
}
