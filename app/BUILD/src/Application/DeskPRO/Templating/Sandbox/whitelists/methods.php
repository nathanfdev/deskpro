<?php

// List of allowed objects and methods

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
        'getUser',
        'isCloud',
        'isDemo',
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
    \Symfony\Component\Form\Form::class => [
        'createView',
    ],
    \DeskPRO\Component\Hierarchy\HierarchyNode::class => SandboxUtils::guessAccessors(
        \DeskPRO\Component\Hierarchy\HierarchyNode::class
    ),
    \DeskPRO\Bundle\AppBundle\Webhooks\WebhookInvocation::class => [
        'getData',
    ],
    \DateTime::class => [
        'getTimestamp',
    ],
    \Application\DeskPRO\Auth\AuthenticationManager::class => SandboxUtils::guessAccessors(
        \Application\DeskPRO\Auth\AuthenticationManager::class
    ),
    \Application\DeskPRO\TicketLayout\LayoutField::class => SandboxUtils::guessAccessors(
        \Application\DeskPRO\TicketLayout\LayoutField::class
    ),
    \Application\DeskPRO\TicketLayout\LayoutDisplay::class => SandboxUtils::guessAccessors(
        \Application\DeskPRO\TicketLayout\LayoutDisplay::class
    ),
    \Symfony\Component\Form\FormError::class => SandboxUtils::guessAccessors(
        \Symfony\Component\Form\FormError::class
    ),
];
