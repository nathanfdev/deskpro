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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutFieldFilter;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketLayout as TicketLayoutModel;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * Class TicketLayoutsController.
 *
 * @ApiModes("all")
 */
class TicketLayoutsController extends BaseController
{
    /**
     * @var LayoutFieldFilter
     */
    protected $filter;

    /**
     * @ApiDoc(
     *     section="Tickets",
     *     description="Get ticket layouts",
     *     requirements={
     *         {"name"="context", "requirement"="agent|user", "dataType"="string", "description"="context for layout"},
     *     },
     *     statusCodes={
     *         200="Returned with list of layouts"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketLayout>"
     * )
     * @Rest\Get("/ticket_layouts/{context}",
     *      name="api_ticket_layouts",
     *      requirements={"context"="(agent|user)"}
     * )
     *
     * @param string $context
     *
     * @return View
     */
    public function listAction($context)
    {
        $ticketLayouts = $this->getTicketLayoutRepository()->findAll();
        if (empty($ticketLayouts)) {
            $ticketLayouts[] = new TicketLayout();
        }

        $response = [];
        foreach ($ticketLayouts as $ticketLayout) {
            $response[] = $this->getContextLayoutResponse($ticketLayout, $context);
        }

        return View::create($response);
    }

    /**
     * @ApiDoc(
     *     section="Tickets",
     *     description="Get ticket department layout for given context",
     *     requirements={
     *         {"name"="departmentId", "requirement"="\d+", "dataType"="integer", "description"="department id for which you want to get layout"},
     *         {"name"="context", "requirement"="agent|user", "dataType"="string", "description"="context of layout"},
     *     },
     *     statusCodes={
     *         200="Returned if everything is ok",
     *         400="Returned if department wasn't found",
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketLayout"
     * )
     * @Rest\Get("/ticket_layouts/{context}/{departmentId}",
     *      name="api_ticket_layout",
     *      requirements={"context"="(agent|user)", "departmentId"="(\d+|default)"}
     * )
     *
     * @param string     $context
     * @param string|int $departmentId
     *
     * @return View
     */
    public function getAction($context, $departmentId = null)
    {
        $department = null;
        if ($departmentId !== 'default') {
            $department = $this->getRepository(Department::class)->find((int) $departmentId);
            if (!$department) {
                throw $this->createNotFoundException();
            }
        }

        $ticketLayout = $this->getTicketLayoutRepository()->findOneBy(['department' => $department]);
        if (!$ticketLayout) {
            $ticketLayout = $this->getTicketLayoutRepository()->findOneBy(['department' => null]);
        }
        if (!$ticketLayout) {
            $ticketLayout = new TicketLayout();
        }

        return View::create($this->getContextLayoutResponse($ticketLayout, $context, $department));
    }

    /**
     * @param TicketLayout $ticketLayout
     * @param string       $context
     * @param Department   $department
     *
     * @return Layout
     */
    protected function getContextLayoutResponse(TicketLayout $ticketLayout, $context, Department $department = null)
    {
        $contextLayout = $context === 'agent' ? $ticketLayout->agent_layout : $ticketLayout->user_layout;
        $department    = $department ?: $ticketLayout->department;

        $this->getLayoutFieldFilter()->filterInvalid($contextLayout);
        $this->get('ticket_layout_factory')->verifyRequiredFields($contextLayout, true);

        return new TicketLayoutModel($contextLayout, $context, $department);
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
        return $this->getRepository(TicketLayout::class);
    }
}
