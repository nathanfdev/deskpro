<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutCollection;
use Application\DeskPRO\TicketLayout\LayoutFieldFilter;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketLayout as TicketLayoutModel;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketLayoutsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_layouts/{context}")
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

        return View::create($this->wrap($response));
    }

    /**
     * @ApiDoc(
     *     section="Tickets",
     *     description="Get ticket layouts JS with compiled fields criteria",
     *     requirements={
     *         {"name"="context", "requirement"="agent|user", "dataType"="string", "description"="context for layout"},
     *     },
     *     statusCodes={
     *         200="Returned with list of layouts"
     *     },
     *     output="string"
     * )
     * @Rest\Get(".js", requirements={"context"="(agent|user)"})
     *
     * @param string $context
     *
     * @return View
     */
    public function listJsAction($context)
    {
        $ticketLayouts = $this->getRepository(TicketLayout::class)->findAll();
        if (empty($ticketLayouts)) {
            $ticketLayouts[] = new TicketLayout();
        }

        $layouts = new LayoutCollection();
        foreach ($ticketLayouts as $ticketLayout) {
            $departmentId = $ticketLayout->getDepartment() ? $ticketLayout->getDepartment()->getId() : null;
            $layouts->addLayout($this->getContextLayout($ticketLayout, $context), $departmentId);
        }

        return new Response($layouts->compileJsObj(true));
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

        return View::create($this->wrap($this->getContextLayoutResponse($ticketLayout, $context, $department)));
    }

    /**
     * @ApiDoc(
     *     section="Tickets",
     *     description="Get ticket department layout JS with compiled fields criteria for given context",
     *     requirements={
     *         {"name"="department", "requirement"="\d+|default", "dataType"="integer|string", "description"="department id for which you want to get layout"},
     *         {"name"="context", "requirement"="agent|user", "dataType"="string", "description"="context of layout"},
     *     },
     *     statusCodes={
     *         200="Returned if everything is ok",
     *         400="Returned if department wasn't found",
     *     },
     *     output="string"
     * )
     * @Rest\Get("/{department}.js", requirements={"context"="(agent|user)", "department"="(\d+|default)"})
     * @ParamConverter(name="department", converter="ticket_layout_department")
     *
     * @param string     $context
     * @param Department $department
     *
     * @return View
     */
    public function getJsAction($context, Department $department = null)
    {
        $ticketLayout  = $this->getTicketLayout($department);
        $contextLayout = $this->getContextLayout($ticketLayout, $context);

        return new Response($contextLayout->compileJsObj());
    }

    /**
     * @param Department|null $department
     *
     * @return TicketLayout
     */
    protected function getTicketLayout(Department $department = null)
    {
        $ticketLayout = $this->getRepository(TicketLayout::class)->findOneBy(['department' => $department]);
        if (!$ticketLayout) {
            $ticketLayout = $this->getRepository(TicketLayout::class)->findOneBy(['department' => null]);
        }
        if (!$ticketLayout) {
            $ticketLayout = new TicketLayout();
        }

        return $ticketLayout;
    }

    /**
     * @param TicketLayout $ticketLayout
     * @param string       $context
     *
     * @return Layout
     */
    protected function getContextLayout(TicketLayout $ticketLayout, $context)
    {
        $contextLayout = $context === 'agent' ? $ticketLayout->getAgentLayout() : $ticketLayout->getUserLayout();

        $this->get('ticket_layout_fields_filter')->filterInvalid($contextLayout);

        $layoutFactory = $this->get('ticket_layout_factory');
        $layoutFactory->verifyRequiredFields($contextLayout, true);

        return $contextLayout;
    }

    /**
     * @param TicketLayout $ticketLayout
     * @param string       $context
     * @param Department   $department
     *
     * @return TicketLayoutModel
     */
    protected function getContextLayoutResponse(TicketLayout $ticketLayout, $context, Department $department = null)
    {
        $contextLayout = $this->getContextLayout($ticketLayout, $context);
        $department    = $department ?: $ticketLayout->getDepartment();

        return new TicketLayoutModel($contextLayout, $context, $department);
    }
}
