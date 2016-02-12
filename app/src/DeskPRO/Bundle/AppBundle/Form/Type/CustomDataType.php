<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
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
use DeskPRO\Bundle\AppBundle\Form\Form\FormFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyNode;
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
     * @var \DeskPRO\Bundle\AppBundle\Form\Form\FormFieldManager
     */
    protected $field_manager;

    /**
     * Constructor.
     *
     * @param FormFieldManager $field_manager
     */
    public function __construct(FormFieldManager $field_manager)
    {
        $this->field_manager = $field_manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'deskpro_custom_data';
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($form->all() as $child) {
            // set it to the first child's label
            if (!$view->vars['help']) {
                $child_help = $child->getConfig()->getOption('help');
                if ($child_help) {
                    $view->vars['help'] = $child_help;
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
    }

    /**
     * Generate form fields.
     *
     * @param FormEvent $event
     */
    public function onGenerateFields(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        /** @var CustomDefAbstract $custom_def */
        $custom_def = $config->getOption('custom_data_field');
        $field      = $this->field_manager->createCustomField(
            $custom_def,
            $config->getOption('agent_interface'),
            $config->getOption('inline')
        );

        // custom fields are implemented as a compound type
        // and this label is for the 'data' attribute, whereas
        // the real label will be on the parent form which is adding the field
        $options = array_merge($field->getOptions(), [
            'label'          => false,
            'help'           => $custom_def->getDescription(),
            'error_bubbling' => true,
            'mapped'         => false,
        ]);

        if ($config->getOption('ignore_validation')) {
            $options = array_merge($options, [
                'validation_groups' => [],
                'constraints'       => null,
            ]);
        }

        $form->add($field->getName(), $field->getType(), $options);

        $data = $this->filterCustomDefData($event->getData(), $custom_def);
        if ($data->count()) {
            $form_field_data = $data->first()->getData();
            if ($custom_def->isChoiceType()) {
                $form_field_data = $data
                    ->map(function (CustomDataAbstract $custom_data) {
                        return $custom_data->getFieldId();
                    })
                    ->toArray()
                ;

                $form_field_data = implode(',', $form_field_data);
            }

            $form_field = $form->get($field->getName());
            $form_field->setData($form_field_data);
        }
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

        /** @var CustomDefAbstract $custom_def */
        $custom_def = $config->getOption('custom_data_field');

        /* @var CustomDataAbstract[]|ArrayCollection $all_custom_data */
        $all_custom_data = $form->getData();
        $custom_def_data = $this->filterCustomDefData($all_custom_data, $custom_def);

        if ($custom_def->isChoiceType()) {
            $data = $form->get('field')->getNormData();
            $data = is_array($data) ? $data : ($data ? [$data] : []);
            $data = array_map(function (HierarchyNode $choice_custom_def) {
                return $choice_custom_def->getData()->getId();
            }, $data);

            $exist = $custom_def_data
                ->map(function (CustomDataAbstract $custom_data) {
                    return $custom_data->field->getId();
                })
                ->toArray()
            ;

            // remove deleted items
            foreach ($custom_def_data as $custom_data) {
                if (!in_array($custom_data->field->getId(), $data)) {
                    $custom_def_data->removeElement($custom_data);
                }
            }

            // add new items
            foreach ($data as $field_id) {
                if (!in_array($field_id, $exist)) {
                    $custom_data = $this->createCustomData($custom_def);
                    $custom_data->setValue(1);
                    $custom_data->setField($custom_def->getChildById($field_id));

                    $custom_def_data->add($custom_data);
                }
            }
        } else {
            if ($custom_def_data->count()) {
                $custom_data = $custom_def_data->first();
                $custom_data->setData($form->get('data')->getNormData());
            } else {
                $custom_data = $this->createCustomData($custom_def);
                $custom_data->setData($form->get('data')->getData());

                $custom_def_data->add($custom_data);
            }
        }

        // Set reference to custom def field.
        foreach ($custom_def_data as $custom_data) {
            $custom_data->root_field = $custom_def;

            if ($custom_def->getType() !== 'choice') {
                // for simple custom data field = root field
                $custom_data->field = $custom_def;
            }
        }

        // Merge custom def data with existing owner custom data collection.
        foreach ($custom_def_data as $custom_data) {
            if (!$all_custom_data->contains($custom_data)) {
                $all_custom_data->add($custom_data);
            }
        }
        foreach ($all_custom_data as $custom_data) {
            if ($custom_data->root_field === $custom_def && !$custom_def_data->contains($custom_data)) {
                $all_custom_data->removeElement($custom_data);
            }
        }

        $event->setData(clone $all_custom_data);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'inline'            => false,
                'error_bubbling'    => false,
                'ignore_validation' => false,
                'fully_hidden'      => function (Options $options) {
                    /** @var \Application\DeskPRO\Entity\CustomDefAbstract $field */
                    $field = $options['custom_data_field'];
                    if ($field) {
                        return $field->getHandlerClass() === 'Application\DeskPRO\CustomFields\Handler\Hidden';
                    }

                    return false;
                },
            ])
            ->setRequired([
                'custom_data_field',
                'agent_interface',
            ])
            ->setAllowedTypes([
                'custom_data_field' => 'Application\DeskPRO\Entity\CustomDefAbstract',
                'agent_interface'   => 'bool',
                'inline'            => 'bool',
            ])
        ;
    }

    /**
     * @param CustomDefAbstract $custom_def
     *
     * @return CustomDataAbstract
     */
    protected function createCustomData(CustomDefAbstract $custom_def)
    {
        if ($custom_def instanceof CustomDefTicket) {
            return new CustomDataTicket();
        } elseif ($custom_def instanceof CustomDefPerson) {
            return new CustomDataPerson();
        } elseif ($custom_def instanceof CustomDefOrganization) {
            return new CustomDataOrganization();
        } elseif ($custom_def instanceof CustomDefFeedback) {
            return new CustomDataFeedback();
        }

        throw new \RuntimeException('Unsupported custom data owner '.get_class($custom_def));
    }

    /**
     * @param Collection        $all_custom_data
     * @param CustomDefAbstract $custom_def
     *
     * @return CustomDataAbstract[]|ArrayCollection
     */
    protected function filterCustomDefData(Collection $all_custom_data, CustomDefAbstract $custom_def)
    {
        $custom_def_data = $all_custom_data->filter(function (CustomDataAbstract $custom_data) use ($custom_def) {
            return $custom_data->root_field === $custom_def;
        });

        if (!$custom_def_data->count()) {
            $default_value = $custom_def->getDefaultValue();
            if ($default_value) {
                $default_custom_data = $this->createCustomData($custom_def);
                $default_custom_data->setData($default_value);

                $custom_def_data->add($default_custom_data);
            }
        }

        return $custom_def_data;
    }
}
