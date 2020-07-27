<?php

// List of allowed objects and properties.
// DO NOT whitelist Request::server unless you have a good reason!

return [
    \DeskPRO\Component\Util\LazyPropObject::class => [
        'alerts',
        'flashes',
        'data',
        'related_content',
        'comments',
        'categories',
        'ymCounts',
        'view',
        'topics_data',
        'is_compact',
    ],
    \Symfony\Component\Form\FormView::class => [
        'children',
        'parent',
        'vars',
    ],
    \Symfony\Component\HttpFoundation\Request::class => [
        'query',
        'attributes',
        'headers',
    ],
    \Symfony\Component\Form\ChoiceList\View\ChoiceView::class => [
        'data',
        'label',
        'value',
        'attr',
    ],
];
