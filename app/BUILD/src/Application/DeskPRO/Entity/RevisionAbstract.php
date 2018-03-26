<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use DateTime;
use JMS\Serializer\Annotation as JMS;

/**
 * Base revisions.
 */
abstract class RevisionAbstract extends DomainObject
{
    const STATUS_VISIBLE    = 'published';
    const STATUS_VALIDATING = 'validating';
    const STATUS_DECLINED   = 'declined';
    const STATUS_DRAFT      = 'draft';

    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * Person who made the revision.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $person = null;

    /**
     * @JMS\Exclude()
     *
     * @var string
     */
    protected $status = 'visible';

    /**
     * DateTime the revision was created.
     *
     * @JMS\Type("DateTime")
     *
     * @var DateTime
     */
    protected $date_created;

    public function __construct()
    {
        $this['date_created'] = new DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
