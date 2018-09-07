<?php

namespace DeskPRO\Bundle\AppBundle\Model;

use DeskPRO\Component\Util\RandUtils;
use JMS\Serializer\Annotation as JMS;

/**
 * Class PopupModel.
 *
 * @JMS\ExclusionPolicy("all")
 */
class PopupModel
{
    const TARGET_TYPE_LIST  = 'list';
    const TARGET_TYPE_QUERY = 'query';

    const DISPLAY_BLOCK_TYPE_HTML   = 'html';
    const DISPLAY_BLOCK_TYPE_PERSON = 'person';
    const DISPLAY_BLOCK_TYPE_ORG    = 'organization';
    const DISPLAY_BLOCK_TYPE_TICKET = 'ticket';

    const DISPLAY_VIEW_TYPE_LIST   = 'list';
    const DISPLAY_VIEW_TYPE_DETAIL = 'detail';

    const ACTION_TYPE_DISMISS = 'dismiss';
    const ACTION_TYPE_TICKET  = 'create_ticket';
    const ACTION_TYPE_WEBHOOK = 'webhook';

    /**
     * @var string
     */
    protected $externalId;

    /**
     * @var array
     */
    protected $actions = [];

    /**
     * @var array
     */
    protected $display = [];

    /**
     * @var array
     */
    protected $target = [];

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $uuid;

    public function __construct()
    {
        $this->uuid = RandUtils::uuidV4();
    }

    /**
     * @return string
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @param string $externalId
     *
     * @return $this
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param array $actions
     *
     * @return $this
     */
    public function setActions(array $actions)
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * @return array
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * @param array $display
     *
     * @return $this
     */
    public function setDisplay(array $display)
    {
        $this->display = $display;

        return $this;
    }

    /**
     * @return array
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param array $target
     *
     * @return $this
     */
    public function setTarget(array $target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    public function getEventData()
    {
        return [
            'target'      => $this->target,
            'display'     => $this->display,
            'actions'     => $this->actions,
            'external_id' => $this->externalId,
        ];
    }
}
