<?php

namespace DeskPRO\Bundle\ImportBundle\Model\ContactData;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AbstractUrlContactData.
 */
abstract class AbstractUrlContactData extends AbstractContactData
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\Url()
     */
    protected $url;

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}
