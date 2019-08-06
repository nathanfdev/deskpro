<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Exporting community topic custom def entity.
 *
 * Class CommunityTopicCustomDef
 *
 * @Assert\GroupSequenceProvider
 */
class CommunityTopicCustomDef extends AbstractCustomDef
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    protected $sys_name;

    /**
     * Returns sys name.
     *
     * @return string
     */
    public function getSysName()
    {
        return $this->sys_name;
    }

    /**
     * Set sys name.
     *
     * @param string $sys_name
     *
     * @return $this
     */
    public function setSysName($sys_name)
    {
        $this->sys_name = $sys_name;

        return $this;
    }
}
