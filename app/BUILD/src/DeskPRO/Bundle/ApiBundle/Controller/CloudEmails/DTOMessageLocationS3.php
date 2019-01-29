<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\CloudEmails;

use JMS\Serializer\Annotation as JMS;

class DTOMessageLocationS3
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $bucketName;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $objectKey;

    /**
     * @return string
     */
    public function getBucketName()
    {
        return $this->bucketName;
    }

    /**
     * @param string $bucketName
     */
    public function setBucketName( $bucketName )
    {
        $this->bucketName = $bucketName;
    }

    /**
     * @return string
     */
    public function getObjectKey()
    {
        return $this->objectKey;
    }

    /**
     * @param string $objectKey
     */
    public function setObjectKey( $objectKey )
    {
        $this->objectKey = $objectKey;
    }


}
