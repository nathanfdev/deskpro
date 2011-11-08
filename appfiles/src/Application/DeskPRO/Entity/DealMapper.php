<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Abdullah Kiser <kiser.bd@gmail.com>
 */
namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * Deal entity definition
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\DealStage")
 * @ORM_Mapping\Table(name="deals_mapper")
 */

class DealMapper extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID
     *
     * @var int
     * @ORM_Mapping\Id
     * @ORM_Mapping\generatedValue(strategy="IDENTITY")
     * @ORM_Mapping\Column(name="id", type="integer")
     *
     */
    protected $id = null;

    /**     
     * @var \Application\DeskPRO\Entity\Deal
     * @ORM_Mapping\ManyToOne(targetEntity="Deal",  cascade={"persist", "remove", "merge"})
     * @ORM_Mapping\JoinColumn(name="dealid", referencedColumnName="id", onDelete="cascade")
     */
    protected $deal;

    /**
     * @var string
     * @ORM_Mapping\Column(name="type", type="string")
     */
    protected $linktype;

    /**
     * @var int
     * @ORM_Mapping\Column(name="typeid", type="integer")
     */
    protected $typeid;

}