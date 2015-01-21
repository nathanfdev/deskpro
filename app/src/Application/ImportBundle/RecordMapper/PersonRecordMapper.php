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

namespace Application\ImportBundle\RecordMapper;

use Application\ImportBundle\Generator\Writer\DeskPro\Importer\Mapper\MapperInterface;
use Doctrine\DBAL\Connection;

/**
 * Class PersonRecordMapper
 * @package Application\ImportBundle\RecordMapper
 */
class PersonRecordMapper implements RecordMapperInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $db;

    /**
     * @var array
     */
    private $cache = array();

    /**
     * Constructor
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return MapperInterface::TYPE_PERSON;
    }

    /**
     * Returns person ID given an email address.
     *
     * @param  mixed    $value
     * @return int|null
     */
    public function findIdFromValue($value)
    {
        $dataArray = $this->fetch($value);
        return isset($dataArray['id']) ? $dataArray['id'] : null;
    }

    /**
     * Checks if the give person is an agent
     *
     * @param  string $email The email to check by
     * @return bool   True if the given user is an agent and false otherwise
     */
    public function checkIsAgent($email)
    {
        $dataArray = $this->fetch($email);
        return (bool) $dataArray['is_agent'];
    }

    /**
     * @param string $email
     * @return null
     */
    protected function fetch($email)
    {
        $query = 'SELECT people.id, people.is_agent, people_emails.email FROM people JOIN people_emails ON people.id = people_emails.person_id WHERE people_emails.email = ?';
        $email = strtolower($email);

        if (!isset($this->cache[$email])) {
            $result = $this->db->fetchAll($query, array($email));

            if (!$result || !isset($result[0])) {
                return null;
            }

            while (count($this->cache) >= 5000) {
                array_shift($this->cache);
            }

            $this->cache[$email] = $result[0];
        }

        return $this->cache[$email];
    }
}
