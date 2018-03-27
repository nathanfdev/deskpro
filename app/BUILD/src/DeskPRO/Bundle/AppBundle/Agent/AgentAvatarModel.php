<?php

namespace DeskPRO\Bundle\AppBundle\Agent;

/**
 * Class AgentAvatarModel.
 */
class AgentAvatarModel
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $display_name;

    /**
     * @var string
     */
    private $pictureUrlTemplate;

    /**
     * Constructor.
     *
     * @param int    $id
     * @param string $displayName
     * @param string $pictureUrlTemplate
     */
    public function __construct($id, $displayName, $pictureUrlTemplate)
    {
        $this->id                 = $id;
        $this->display_name       = $displayName;
        $this->pictureUrlTemplate = $pictureUrlTemplate;
    }

    /**
     * @param int $size
     *
     * @return string
     */
    public function getPictureUrl($size)
    {
        return $this->pictureUrlTemplate ? str_replace(urlencode('{{size}}'), $size, $this->pictureUrlTemplate) : null;
    }
}
