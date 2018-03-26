<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class ProfileAvatar.
 */
class ProfileAvatar
{
    /**
     * Auth identity for this blob.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $blob_auth_id;

    /**
     * Actual url with blob data.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $url;

    /**
     * ProfileAvatar constructor.
     *
     * @param $blob_auth_id
     * @param $url
     */
    public function __construct($blob_auth_id, $url)
    {
        $this->blob_auth_id = $blob_auth_id;
        $this->url          = $url;
    }
}
