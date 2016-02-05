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
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutFieldFilter;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiTags;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TicketLayoutsController.
 *
 * @ApiModes("all")
 * @ApiTags("agent.tickets.ticket_layouts")
 */
class TicketLayoutsController extends BaseController
{
    /**
     * @var LayoutFieldFilter
     */
    protected $filter;

    /**
     * @ApiDoc(
     *      description="Get ticket layouts",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/ticket_layouts/{context}",
     *      name="api_ticket_layouts",
     *      requirements={"context"="(agent|user)"}
     * )
     *
     * @param string $context
     *
     * @return View
     */
    public function cgetAction($context)
    {
        $ticket_layouts = $this->getTicketLayoutRepository()->findAll();
        if (empty($ticket_layouts)) {
            $ticket_layouts[] = new TicketLayout();
        }

        $response = [];
        foreach ($ticket_layouts as $ticket_layout) {
            $response[] = $this->getContextLayoutResponse($ticket_layout, $context);
        }

        return View::create($response);
    }

    /**
     * @ApiDoc(
     *      description="Get ticket department layout",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/ticket_layouts/{context}/{department_id}",
     *      name="api_ticket_layout",
     *      requirements={"context"="(agent|user)", "department_id"="(\d+|default)"}
     * )
     *
     * @param string     $context
     * @param string|int $department_id
     *
     * @return View
     */
    public function getAction($context, $department_id = null)
    {
        $department = null;
        if ($department_id !== 'default') {
            $department = $this->getRepository('DeskPRO:Department')->find((int) $department_id);
            if (!$department) {
                throw new NotFoundHttpException();
            }
        }

        $ticket_layout = $this->getTicketLayoutRepository()->findOneBy(['department' => $department]);
        if (!$ticket_layout) {
            $ticket_layout = $this->getTicketLayoutRepository()->findOneBy(['department' => null]);
        }
        if (!$ticket_layout) {
            $ticket_layout = new TicketLayout();
        }

        return View::create($this->getContextLayoutResponse($ticket_layout, $context, $department));
    }

    /**
     * @param TicketLayout $ticket_layout
     * @param string       $context
     * @param Department   $department
     *
     * @return Layout
     */
    protected function getContextLayoutResponse(TicketLayout $ticket_layout, $context, Department $department = null)
    {
        $context_layout = $context === 'agent' ? $ticket_layout->agent_layout : $ticket_layout->user_layout;
        $department     = $department ?: $ticket_layout->department;

        $this->getLayoutFieldFilter()->filterInvalid($context_layout);

        $fields = [];
        foreach ($context_layout->all() as $field) {
            $fields[] = $field->exportToArray();
        }

        return [
            'department' => $department ? $department->getId() : null,
            'context'    => $context,
            'fields'     => $fields,
        ];
    }

    /**
     * @return LayoutFieldFilter
     */
    protected function getLayoutFieldFilter()
    {
        if (!$this->filter) {
            $this->filter = new LayoutFieldFilter(
                $this->getContainer()->getTicketFieldManager(),
                $this->getContainer()->getPersonFieldManager()
            );
        }

        return $this->filter;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getTicketLayoutRepository()
    {
        return $this->getRepository('DeskPRO:TicketLayout');
    }
}
