<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

namespace Application\ImportBundle\Generator\Writer\DeskPro\Importer\Mapper;

use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;

/**
 * Email account record mapper
 *
 * Class EmailAccount
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer\Mapper
 */
final class EmailAccount implements MapperInterface
{
    /**
     * @var EmailAccountManager
     */
    private $manager;

    /**
     * Constructor
     *
     * @param EmailAccountManager $manager
     */
    public function __construct(EmailAccountManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_EMAIL_ACCOUNT;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, $throw_exception = true)
    {
        if (count($criteria) !== 1 || empty($criteria['email'])) {
            throw new \Exception('Invalid criteria');
        }

        $record = $this->manager->findAccountForEmailAddress($criteria['email']);
        if ( ! $record && $throw_exception) {
            throw new MapperException('Email account not found', $criteria);
        }

        return $record;
    }

    /**
     * Returns the existing email account by email
     *
     * @param string $email
     * @param bool   $throw_exception
     *
     * @return \Application\DeskPRO\Entity\EmailAccount|null
     * @throws MapperException
     */
    public function findOneByEmail($email, $throw_exception = true)
    {
        return $this->findOneBy(array('email' => $email), $throw_exception);
    }
}
