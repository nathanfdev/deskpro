<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Exporter\Parser\OsTicket;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Generator\Exporter\Parser\PeopleStorage;
use Application\ImportBundle\Reader\OsTicket\OsTicketReaderInterface;
use DateTimeZone;

/**
 * OsTicket people parser
 *
 * Class People
 * @package Application\ImportBundle\Generator\Exporter\Parser\OsTicket
 */
final class People extends AbstractParser
{
    /**
     * @var int
     */
    private $staff_min_id = 0;

    /**
     * @var int
     */
    private $users_min_id = 0;

    /**
     * @var PeopleStorage
     */
    private $people_storage;

    /**
     * Constructor
     *
     * @param OsTicketReaderInterface $reader
     * @param FormatterInterface      $formatter
     * @param PeopleStorage           $people_storage
     */
    public function __construct(OsTicketReaderInterface $reader, FormatterInterface $formatter, PeopleStorage $people_storage)
    {
        parent::__construct($reader, $formatter);
        $this->people_storage = $people_storage;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_PERSON;
    }

    /**
     * Returns current staff offset
     *
     * @return int
     */
    public function getCurrentStaffMinId()
    {
        return $this->staff_min_id ? : $this->getBatchConfig()->getStaffMinId();
    }

    /**
     * Returns current users offset
     *
     * @return int
     */
    public function getCurrentUsersMinId()
    {
        return $this->users_min_id ? : $this->getBatchConfig()->getUsersMinId();
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->reader->getStaffCount($this->getCurrentStaffMinId())
             + $this->reader->getUsersCount($this->getCurrentUsersMinId());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $this->entities_loaded = 0;

        $collection = new Entity\Collection();
        $collection
            ->merge($this->exportStaffCollection())
            ->merge($this->exportUsersCollection())
        ;

        return $collection;
    }

    /**
     * Return a collection of staff
     *
     * @return Entity\Collection
     */
    protected function exportStaffCollection()
    {
        $collection = new Entity\Collection();

        do {
            $batch  = $this->reader->findStaff($this->getReaderBatchSize(), $this->getCurrentStaffMinId());
            $config = new ExportCollectionConfig();
            $config
                ->setData($batch)
                ->setPrefix('OSStaff')
                ->setRefColumn('staff_id')
                ->setMethod('exportStaff')
                ->setAdvanceProgressbar(true)
            ;

            $collection->merge($this->exportCollection($config));

            $this->entities_loaded += count($batch);
            $this->staff_min_id     = max($this->staff_min_id, $collection->getMaxOid());

        } while (count($batch) > 0);

        return $collection;
    }

    /**
     * Returns a staff person entity
     *
     * @param array $data
     * @return Entity\Person|null
     */
    protected function exportStaff(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'staff_id'    => TransformerInterface::TYPE_INT,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'staff_',
                'ref'    => 'staff_id',
            )),
            'firstname'   => TransformerInterface::TYPE_STRING,
            'lastname'    => TransformerInterface::TYPE_STRING,
            'timezone_id' => TransformerInterface::TYPE_INT,
            'created'     => TransformerInterface::TYPE_DATE,
            'email'       => TransformerInterface::TYPE_STRING,
            'isadmin'     => TransformerInterface::TYPE_BOOLEAN,
            'group_id'    => TransformerInterface::TYPE_INT,
        ));

        $entity = new Entity\Person();
        $entity
            ->setDestination($formatted['destination'])
            ->setOid($formatted['staff_id'])
            ->setAsAgent(true)
            ->setName($formatted['firstname'] . ' ' . $formatted['lastname'])
            ->setFirstName($formatted['firstname'])
            ->setLastName($formatted['lastname'])
            ->setTimezone(new DateTimeZone($this->reader->findTimezoneById($formatted['timezone_id'])))
            ->setDateCreated($formatted['created'])
            ->setAsAdmin($formatted['isadmin'])
            ->addEmail($formatted['email'])
            ->addUserGroup($this->reader->findUserGroupNameById($formatted['group_id']))
        ;

        return $entity;
    }

    /**
     * Return a collection of users
     *
     * @return Entity\Collection
     */
    protected function exportUsersCollection()
    {
        $collection = new Entity\Collection();

        do {
            $batch = $this->reader->findUsers($this->getReaderBatchSize(), $this->getCurrentUsersMinId());
            $config = new ExportCollectionConfig();
            $config
                ->setData($batch)
                ->setPrefix('OSUser')
                ->setRefColumn('user_id')
                ->setMethod('exportUser')
                ->setAdvanceProgressbar(true)
            ;

            $collection->merge($this->exportCollection($config));

            $this->entities_loaded += count($batch);
            $this->users_min_id    = max($this->users_min_id, $collection->getMaxOid());

        } while (count($batch) > 0);

        return $collection;
    }

    /**
     * Returns an user person entity
     *
     * @param array $data
     * @return Entity\Person|null
     */
    protected function exportUser(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'user_id'     => TransformerInterface::TYPE_INT,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'user_',
                'ref'    => 'user_id',
            )),
            'name'        => TransformerInterface::TYPE_STRING,
            'org_id'      => TransformerInterface::TYPE_INT,
            'created'     => TransformerInterface::TYPE_DATE,
            'address'     => TransformerInterface::TYPE_STRING,
        ));

        $entity = new Entity\Person();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['user_id'])
            ->setAsUser(true)
            ->setName($formatted['name'])
            ->setOrganization($this->reader->findOrganizationNameById($formatted['org_id']))
            ->setDateCreated($formatted['created'])
            ->addEmail($formatted['address'])
        ;

        return $entity;
    }
}
