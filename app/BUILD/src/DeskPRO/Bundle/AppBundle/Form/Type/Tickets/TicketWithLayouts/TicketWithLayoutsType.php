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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use Doctrine\ORM\EntityManager;
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
     * @var EntityManager
     */
    private $em;

    /**
     * @var HierarchyGenerator
     */
    private $hierarchyGenerator;

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param HierarchyGenerator $hierarchyGenerator
     */
    public function __construct(EntityManager $em, HierarchyGenerator $hierarchyGenerator)
    {
        $this->em                 = $em;
        $this->hierarchyGenerator = $hierarchyGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onSetDefaultDepartment'], 100);
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
            ])
            ->setRequired([
                'person',

                // always require to set this props in the controller (no default options) to make sure that we get the form configuration as we expected
                // the form fields are very dependant on the visibility context ('new', 'edit') and layout type ('agent' or 'user')
                'ticket_visibility',
                'ticket_view_context',
            ])
            ->addAllowedValues([
                'ticket_visibility' => [
                    TicketWithLayoutsContext::VISIBILITY_NEW,
                    TicketWithLayoutsContext::VISIBILITY_EDIT,
                    TicketWithLayoutsContext::VISIBILITY_VIEW,
                ],
                'ticket_view_context' => [
                    TicketWithLayoutsContext::VIEW_USER,
                    TicketWithLayoutsContext::VIEW_AGENT,
                ],
            ])
            ->setAllowedTypes([
                'person'        => Person::class,
                'department_id' => ['null', 'integer'],
            ])
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
    public function onSetDefaultDepartment(FormEvent $event)
    {
        /** @var Ticket $data */
        $data   = $event->getData();
        $config = $event->getForm()->getConfig();

        if ($config->getOption('department_id')) {
            $hierarchy  = $this->hierarchyGenerator->generateTicketDepartmentsHierarchy($config->getOption('person'));
            $choiceList = $hierarchy->getChoiceList();

            $choice = current($choiceList->getChoicesForValues([$config->getOption('department_id')]));
            if ($choice) {
                $data->setDepartment($choice->getData());
            }
        }
    }
}
