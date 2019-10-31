<?php

namespace DeskPRO\Bundle\AppBundle\Entity\ObjectAlias;

use Application\DeskPRO\Entity\CustomDefArticle;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\Repository")
 * @JMS\ExclusionPolicy("all")
 */
class CustomArticleFieldDefinitionAlias extends AbstractAlias
{
    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\CustomDefArticle", inversedBy="aliases")
     * @ORM\JoinColumn(name="custom_def_article_id", referencedColumnName="id", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\CustomDefArticle>")
     *
     * @var CustomDefArticle
     */
    private $object;

    /**
     * @return CustomDefArticle
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param CustomDefArticle $object
     */
    public function setObject(CustomDefArticle $object)
    {
        $this->object = $object;
    }

    /**
     * @param mixed $object
     *
     * @return bool
     */
    public function tryAndSetObject($object)
    {
        try {
            $this->setObject($object);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return CustomDefArticle::class;
    }

    /**
     * @return string
     */
    public function getObjectId()
    {
        if ($this->object) {
            return (string) $this->object->getId();
        }
    }
}
