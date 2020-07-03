<?php

return [
    \DeskPRO\Component\Util\LazyPropObject::class => [
        'alerts',
        'flashes',
        'data',
        'related_content',
        'comments',
    ],
    \Symfony\Component\Form\FormView::class => [
        'children',
        'parent',
        'vars',
    ],
    \Symfony\Component\HttpFoundation\Request::class => [
        'query',
        'attributes',
    ],
    \Symfony\Component\Form\ChoiceList\View\ChoiceView::class => [
        'data',
        'label',
        'value',
        'attr',
    ],
];
