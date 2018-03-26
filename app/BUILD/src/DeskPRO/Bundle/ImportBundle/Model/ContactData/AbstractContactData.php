<?php

namespace DeskPRO\Bundle\ImportBundle\Model\ContactData;

use DeskPRO\Bundle\ImportBundle\Model\OidAwareModelInterface;
use DeskPRO\Bundle\ImportBundle\Model\OidAwareModelTrait;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractContactData.
 */
abstract class AbstractContactData implements OidAwareModelInterface
{
    use OidAwareModelTrait;

    /**
     * Comment attached to contact.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $comment;

    /**
     * @return string
     */
    abstract public function getContactType();

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }
}
