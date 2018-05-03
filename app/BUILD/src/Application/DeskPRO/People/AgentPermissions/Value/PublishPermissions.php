<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\AgentPermissions\Value;

class PublishPermissions implements PermissionValueInterface
{
    /** @var bool */
    public $use = false;
    /** @var bool */
    public $create = false;
    /** @var bool */
    public $delete = false;
    /** @var bool */
    public $edit = false;
    /** @var bool */
    public $validate = false;
    /** @var bool */
    public $articles_create_labels = false;
    /** @var bool */
    public $downloads_create_labels = false;
    /** @var bool */
    public $news_create_labels = false;
    /** @var bool */
    public $feedback_create_labels = false;
    /** @var bool */
    public $can_insert_html = false;

    public function getNames()
    {
        return ['use', 'create', 'delete', 'edit', 'validate', 'articles_create_labels', 'downloads_create_labels', 'news_create_labels', 'feedback_create_labels', 'can_insert_html'];
    }

    public function getDestructiveNames()
    {
        return ['delete', 'can_insert_html'];
    }
}
