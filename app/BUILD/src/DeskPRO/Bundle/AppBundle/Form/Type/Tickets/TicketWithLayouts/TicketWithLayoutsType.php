<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Form\BrandFormHelper;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer\FieldRendererInterface;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver\AbstractFieldResolver;
use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\DefaultDepartmentSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketWithLayoutsType.
 */
class TicketWithLayoutsType extends AbstractType
{
    /**
     * @var HierarchyGenerator
     */
    private $hierarchyGenerator;

    /**
     * @var BrandFormHelper
     */
    protected $brandHelper;

    /**
     * Constructor.
     *
     * @param HierarchyGenerator $hierarchyGenerator
     * @param BrandFormHelper    $brandHelper
     */
    public function __construct(HierarchyGenerator $hierarchyGenerator, BrandFormHelper $brandHelper)
    {
        $this->hierarchyGenerator = $hierarchyGenerator;
        $this->brandHelper        = $brandHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'    => Ticket::class,
                'department_id' => null,
                'error_mapping' => [
                    'messages[0].attachments' => FormFields::ATTACHMENTS,
                    'messages[0].message'     => FormFields::MESSAGE.'.message',
                ],
            ])
            ->setRequired([
                'person',

                // always require to set this props in the controller (no default options) to make sure that we get the form configuration as we expected
                // the form fields are very dependant on the visibility context ('new', 'edit') and layout type ('agent' or 'user')
                'ticket_visibility',
                'ticket_view_context',

                'field_resolver',
                'field_renderer',
                'layout_factory',
            ])
            ->setAllowedValues('ticket_visibility', [
                TicketWithLayoutsContext::VISIBILITY_NEW,
                TicketWithLayoutsContext::VISIBILITY_EDIT,
                TicketWithLayoutsContext::VISIBILITY_VIEW,
            ])
            ->setAllowedValues('ticket_view_context', [
                TicketWithLayoutsContext::VIEW_USER,
                TicketWithLayoutsContext::VIEW_AGENT,
            ])
            ->setAllowedTypes('person', Person::class)
            ->setAllowedTypes('department_id', ['null', 'integer'])
            ->setAllowedTypes('field_resolver', AbstractFieldResolver::class)
            ->setAllowedTypes('field_renderer', FieldRendererInterface::class)
            ->setAllowedTypes('layout_factory', 'callable')
        ;
    }

    /**
     * Set department from options.
     * Used to set default department from request.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        // assign ticket to the current brand
        /** @var Ticket $data */
        $data = $event->getData();
        if (!$data->getBrand()) {
            $data->setBrand($this->brandHelper->getCurrentBrand());
        }

        $config  = $event->getForm()->getConfig();
        $options = $config->getOptions();

        // set ticket person if not defined
        if ($data && !$data->getPerson()) {
            $data->setPerson($options['person']);
        }

        // department is already chosen, no need to select the default one
        if ($data->getDepartment()) {
            return;
        }

        $departmentsHierarchy = $this->hierarchyGenerator->generateTicketDepartmentsHierarchy($options['person'], $data);

        if ($config->getOption('department_id')) {
            $choice = current($departmentsHierarchy->getChoiceLoader()->loadChoicesForValues([$config->getOption('department_id')]));
            if ($choice) {
                $data->setDepartment($choice->getData());
            }
        } else {
            $data->setDepartment($this->brandHelper->getDefaultDepartment(DefaultDepartmentSettings::DEFAULT_DEPARTMENT_USER_TYPE));
        }

        // if there is only one department we want to make sure to set it now...

        // if there is only one dep, and ticket has no dep, just set it on the ticket (we won't be showing the widget)
        if (!$data->getDepartment()) {
            if ($departmentsHierarchy->countSelectable() === 1) {
                $data->setDepartment($departmentsHierarchy->getFirstSelectable());
            }
        }
    }
}
