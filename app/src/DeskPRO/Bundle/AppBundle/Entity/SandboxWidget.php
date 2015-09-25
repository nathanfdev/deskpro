<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Entity;

use DeskPRO\Bundle\AppBundle\Doctrine\NotifyPropertyChangeEntity;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * THIS IS A TEST ENTITY and is only here temporarily to show how the API works (we also run unit tests
 * against it).  This data is NOT used in the actual deskpro app.
 *
 * @ORM\Entity()
 * @ORM\Table("api_sandbox_widgets")
 * @Serializer\ExclusionPolicy("ALL")
 *
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route("api_sandbox_widgets_get", parameters={"id" = "expr(object.getId())"})
 * )
 */
class SandboxWidget extends NotifyPropertyChangeEntity
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     * @Serializer\Expose()
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Serializer\Expose()
     * @Assert\NotNull()
     * @Assert\Length(min=10)
     */
    protected $name;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     * @Assert\NotNull()
     * @Assert\NotBlank()
     * @Assert\Type("integer")
     */
    protected $inventory;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->setModelField('name', $name);
    }

    /**
     * @return mixed
     */
    public function getInventory()
    {
        return $this->inventory;
    }

    /**
     * @param mixed $inventory
     */
    public function setInventory($inventory)
    {
        $this->setModelField('inventory', $inventory);
    }
}
