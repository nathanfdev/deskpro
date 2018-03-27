<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\TicketLayout;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * BC TicketLayoutsController until 20170601 version.
 * Added missing "$this->wrap()" method to support layout js sideloading.
 *
 * @ApiModes("all")
 * @Rest\Route("/20170401/ticket_layouts/{context}")
 */
class TicketLayouts20170401Controller extends TicketLayoutsController
{
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
     * @Rest\Get("", requirements={"context"="(agent|user)"})
     *
     * @param string $context
     *
     * @return View
     */
    public function listAction($context)
    {
        $ticketLayouts = $this->getRepository(TicketLayout::class)->findAll();
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
     *         {"name"="department", "requirement"="\d+|default", "dataType"="integer|string", "description"="department id for which you want to get layout"},
     *         {"name"="context", "requirement"="agent|user", "dataType"="string", "description"="context of layout"},
     *     },
     *     statusCodes={
     *         200="Returned if everything is ok",
     *         400="Returned if department wasn't found",
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketLayout"
     * )
     * @Rest\Get("/{department}", requirements={"context"="(agent|user)", "department"="(\d+|default)"})
     * @ParamConverter(name="department", converter="ticket_layout_department")
     *
     * @param string     $context
     * @param Department $department
     *
     * @return View
     */
    public function getAction($context, Department $department = null)
    {
        $ticketLayout = $this->getTicketLayout($department);

        return View::create($this->getContextLayoutResponse($ticketLayout, $context, $department));
    }
}
