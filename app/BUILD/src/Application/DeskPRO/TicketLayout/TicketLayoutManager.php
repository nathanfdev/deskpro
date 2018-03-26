<?php

/**
 * DeskPRO.
 *
 * @category TicketLayout
 */

namespace Application\DeskPRO\TicketLayout;

use Application\DeskPRO\App;

class TicketLayoutManager
{
    /**
     * Layouts keyed by department ID.
     *
     * @var LayoutCollection
     */
    private $user_layouts;

    /**
     * Layouts keyed by deartmend ID.
     *
     * @var LayoutCollection
     */
    private $agent_layouts;

    /**
     * @param \Application\DeskPRO\Entity\TicketLayout[] $ticket_layouts
     *
     * @return TicketLayoutManager
     */
    public static function createWithLayoutRecords(array $ticket_layouts)
    {
        $user_layouts  = new LayoutCollection();
        $agent_layouts = new LayoutCollection();

        foreach ($ticket_layouts as $l) {
            $key = $l->department ? $l->department->getId() : null;
            if ($user_layout = $l->user_layout) {
                LayoutUtil::ensureMinimumUserLayout($user_layout);
                $user_layouts->addLayout($user_layout, $key);
            }
            if ($agent_layout = $l->agent_layout) {
                LayoutUtil::ensureMinimumAgentLayout($agent_layout);
                $agent_layouts->addLayout($agent_layout, $key);
            }
        }

        return new self($user_layouts, $agent_layouts);
    }

    /**
     * @param array $ticket_layouts
     *
     * @return TicketLayoutManager
     */
    public static function createWithLayoutArrays(array $ticket_layouts)
    {
        $user_layouts  = new LayoutCollection();
        $agent_layouts = new LayoutCollection();

        foreach ($ticket_layouts as $l) {
            $key = $l['department_id'] ? $l['department_id'] : null;
            if (!empty($l['user_layout'])) {
                $user_layout = $l['user_layout'];
                LayoutUtil::ensureMinimumUserLayout($user_layout);
                $user_layouts->addLayout($user_layout, $key);
            }
            if (!empty($l['agent_layout'])) {
                $agent_layout = $l['agent_layout'];
                LayoutUtil::ensureMinimumAgentLayout($agent_layout);
                $agent_layouts->addLayout($agent_layout, $key);
            }
        }

        return new self($user_layouts, $agent_layouts);
    }

    /**
     * @param LayoutCollection $user_layouts
     * @param LayoutCollection $agent_layouts
     */
    public function __construct(LayoutCollection $user_layouts, LayoutCollection $agent_layouts)
    {
        $this->user_layouts  = $user_layouts;
        $this->agent_layouts = $agent_layouts;
    }

    /**
     * @param bool $newPortal
     *
     * @return LayoutCollection
     */
    public function getUserLayouts($newPortal = false)
    {
        // this is a bit of a hack for now. we should deprecate this service in favor of TicketLayoutFactory in portal.
        if ($newPortal) {
            App::$container->get('ticket_layout_factory')->prepareUserMultipleLayouts($this->user_layouts);
        }

        return $this->user_layouts;
    }

    /**
     * @return LayoutCollection
     */
    public function getAgentLayouts()
    {
        return $this->agent_layouts;
    }

    /**
     * @return array
     */
    public function getUserLayoutItems()
    {
        return $this->_collectLayoutItems($this->user_layouts);
    }

    /**
     * @return array
     */
    public function getAgentLayoutItems()
    {
        return $this->_collectLayoutItems($this->user_layouts);
    }

    /**
     * @param LayoutCollection $coll
     *
     * @return array
     */
    private function _collectLayoutItems(LayoutCollection $coll)
    {
        $items = [];

        foreach ($coll as $layout) {
            foreach ($layout as $item) {
                if (!isset($items[$item->getId()])) {
                    $items[$item->getId()] = [
                        'id'           => $item->getId(),
                        'field_type'   => $item->getFieldType(),
                        'field_id'     => $item->getFieldId(),
                        'has_criteria' => false,
                    ];
                }

                if ($item->hasCriteria()) {
                    $items[$item->getId()]['has_criteria'] = true;
                }
            }
        }

        return $items;
    }
}
