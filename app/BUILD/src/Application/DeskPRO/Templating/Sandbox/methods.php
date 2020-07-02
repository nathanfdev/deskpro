<?php

use Application\DeskPRO\Templating\Sandbox\SandboxUtils;

return [
    \Symfony\Component\HttpFoundation\Request::class => [
        'getBasePath',
        'getSchemeAndHttpHost',
        'get',
    ],
    \Symfony\Component\HttpFoundation\ParameterBag::class => [
        'get',
        'all',
    ],
    \Application\DeskPRO\Templating\GlobalVariables::class => [
        'isPortalEnabled',
        'getSetting',
    ],
    \Doctrine\ORM\PersistentCollection::class => [
        'count',
    ],
    \Application\DeskPRO\Twig\AppVariable::class => [
        'getUser',
        'hasBeta',
        'getSetting',
        'getRequest',
    ],
    \DeskPRO\Bundle\PortalBundle\View\HelpCenterData::class => SandboxUtils::guessGetters(
        \DeskPRO\Bundle\PortalBundle\View\HelpCenterData::class
    ),
    \Pagerfanta\Pagerfanta::class => [
        'getNbPages',
        'haveToPaginate',
    ],
    \Doctrine\Common\Collections\ArrayCollection::class => [
        '__toString',
    ],
    \Symfony\Component\Form\FormView::class => [
        'isRendered',
        'isMethodRendered',
        'setMethodRendered',
    ],
    \Symfony\Component\Form\FormErrorIterator::class => [
        '__toString',
    ],
    \DeskPRO\Component\Hierarchy\HierarchyNode::class => SandboxUtils::guessGetters(
        \DeskPRO\Component\Hierarchy\HierarchyNode::class
    ),
    \DeskPRO\Bundle\PortalBundle\View\Ticket\TicketListTable::class => SandboxUtils::guessGetters(
        \DeskPRO\Bundle\PortalBundle\View\Ticket\TicketListTable::class
    ),
    \DeskPRO\Bundle\AppBundle\Model\TicketColumn::class => SandboxUtils::guessGetters(
        \DeskPRO\Bundle\AppBundle\Model\TicketColumn::class
    ),
    \DeskPRO\Bundle\PortalBundle\Model\TicketFilter::class => SandboxUtils::guessGetters(
        \DeskPRO\Bundle\PortalBundle\Model\TicketFilter::class
    ),
    \DeskPRO\Bundle\AppBundle\Model\TicketView::class => SandboxUtils::guessGetters(
        \DeskPRO\Bundle\AppBundle\Model\TicketView::class
    ),
    \DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line\UserMessageLine::class => SandboxUtils::guessGetters(
        \DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line\UserMessageLine::class
    ),
    \DeskPRO\Bundle\AppBundle\Model\TicketViewProperty::class => SandboxUtils::guessGetters(
        \DeskPRO\Bundle\AppBundle\Model\TicketViewProperty::class
    ),
];
