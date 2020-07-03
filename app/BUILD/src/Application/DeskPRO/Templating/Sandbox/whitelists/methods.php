<?php

// List of whitelisted arbitrary objects and methods

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
    \DeskPRO\Bundle\PortalBundle\View\HelpCenterData::class => SandboxUtils::guessAccessors(
        \DeskPRO\Bundle\PortalBundle\View\HelpCenterData::class
    ),
    \Pagerfanta\Pagerfanta::class => [
        'getNbPages',
        'haveToPaginate',
    ],
    \Doctrine\Common\Collections\ArrayCollection::class => [
        '__toString',
    ],
    \Symfony\Component\Form\FormView::class => SandboxUtils::guessAccessors(
        \Symfony\Component\Form\FormView::class,
        [
            'setMethodRendered',
        ]
    ),
    \Symfony\Component\Form\FormErrorIterator::class => [
        '__toString',
    ],
    \DeskPRO\Component\Hierarchy\HierarchyNode::class => SandboxUtils::guessAccessors(
        \DeskPRO\Component\Hierarchy\HierarchyNode::class
    ),
    \DeskPRO\Bundle\PortalBundle\View\Ticket\TicketListTable::class => SandboxUtils::guessAccessors(
        \DeskPRO\Bundle\PortalBundle\View\Ticket\TicketListTable::class
    ),
    \DeskPRO\Bundle\AppBundle\Model\TicketColumn::class => SandboxUtils::guessAccessors(
        \DeskPRO\Bundle\AppBundle\Model\TicketColumn::class
    ),
    \DeskPRO\Bundle\PortalBundle\Model\TicketFilter::class => SandboxUtils::guessAccessors(
        \DeskPRO\Bundle\PortalBundle\Model\TicketFilter::class
    ),
    \DeskPRO\Bundle\AppBundle\Model\TicketView::class => SandboxUtils::guessAccessors(
        \DeskPRO\Bundle\AppBundle\Model\TicketView::class
    ),
    \DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line\UserMessageLine::class => SandboxUtils::guessAccessors(
        \DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line\UserMessageLine::class
    ),
    \DeskPRO\Bundle\AppBundle\Model\TicketViewProperty::class => SandboxUtils::guessAccessors(
        \DeskPRO\Bundle\AppBundle\Model\TicketViewProperty::class
    ),
    \DeskPRO\Bundle\AppBundle\Webhooks\WebhookInvocation::class => [
        'getData',
    ],
    \DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line\TicketCreatedLine::class => SandboxUtils::guessAccessors(
    \DeskPRO\Bundle\AppBundle\Ticket\Timeline\Line\TicketCreatedLine::class
    ),
    \DeskPRO\Bundle\AppBundle\Ticket\Timeline\TicketTimeline::class => SandboxUtils::guessAccessors(
        \DeskPRO\Bundle\AppBundle\Ticket\Timeline\TicketTimeline::class
    ),
    \DateTime::class => [
        'getTimestamp',
    ],
    \Application\DeskPRO\Auth\AuthenticationManager::class => SandboxUtils::guessAccessors(
        \Application\DeskPRO\Auth\AuthenticationManager::class
    ),
];
