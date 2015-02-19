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

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;
use Application\DeskPRO\Entity;
use Orb\Util\Arrays;

/**
 * This finds a user based on the email sent, or creates a new user
 * from it.
 */
class PersonFromEmailProcessor
{
    private $is_running = false;

    /**
     * @var string
     */
    public $creation_system = 'gateway.person';

    /**
     * When we have any email from a user, perform basic routines on the user its from.
     *
     * @param \Application\DeskPRO\Entity\Person $person
     */
    public function passPerson(EmailAddress $from, Entity\Person $person)
    {
        if (!$person['first_name'] AND !$person['last_name']) {
            if ($from->getName()) {
                $person['name'] = $from->getName();
                App::getOrm()->persist($person);
            }
        }
    }

    /**
     * Finds a person based on the From in the email address.
     *
     * @param  EmailAddress                            $from
     * @return \Application\DeskPRO\Entity\Person|null
     */
    public function findPerson(EmailAddress $from)
    {
        // Find in local db first
        $person = App::getEntityRepository('DeskPRO:Person')->findOneByEmail($from->getEmail());
        if ($person) {
            return $person;
        }

        /** @var \Application\DeskPRO\Usersource\UsersourceManager $um */
        $um = App::getSystemService('usersource_manager');

        if ($person = $um->findPersonByEmail($from->getEmail())) {
            $this->passPerson($from, $person);

            return $person;
        }

        return null;
    }

    /**
     * Finds a person based on the From in the email address.
     *
     * @param  string                             $email_address The email address as a string
     * @param  string|null $name
     * @return \Application\DeskPRO\Entity\Person
     */
    public function findPersonByEmailAddress($email_address, $name = null)
    {
        $email        = new EmailAddress();
        $email->email = $email_address;

        if ($name) {
            $email->name = $name;
            $email->name_utf8 = $name;
        }

        return $this->findPerson($email);
    }


    /**
     * @param $email_address
     * @param null $name
     * @return Entity\Person
     */
    public function createPersonByEmailAddress($email_address, $name = null)
    {
        $email = new EmailAddress();
        $email->email = $email_address;

        if ($name) {
            $email->name = $name;
            $email->name_utf8 = $name;
        }

        return $this->createPerson($email, true);
    }



    /**
     * Creates a person based on the From email address.
     *
     * This should NOT be called within a transaction because the record needs to be committed so we can be sure it
     * is properly saved.
     *
     * @param $from
     * @param  bool                               $do_validated True to validate user, false to use whatever is default
     * @return \Application\DeskPRO\Entity\Person
     */
    public function createPerson(EmailAddress $from, $do_validated = false)
    {
        $person = App::getEntityRepository('DeskPRO:Person')->findOneByEmail($from->getEmail(), true);
        if ($person) {
            return $person;
        }

        $db = App::getDb();

        $last_e = null;

        // - Creating a new user can often result in duplicate key errors on the email address
        // because two processes might try to commit the same row at the same time.
        // - To prevent this, this method should be called OUTSIDE of a transaction (so the result is available immediately).
        // - We insert raw records outside of Doctrine because an error during a normal Doctrine flush would
        // result in the EM being closed and that is not recoverable.

        $db->beginTransaction();
        try {
            $tmp_person = Entity\Person::newContactPerson(array(
                'creation_system'    => $this->creation_system,
                'name'               => $from->getNameUtf8() ?: '',
                'is_confirmed'       => 1,
                'is_agent_confirmed' => App::getSetting('core.agent_validation') ? 0 : 1
            ));

            // Create new person record (no chance of conflicts here)
            $p_array = $tmp_person->toArray(Entity\Person::TOARRAY_ONLY_PRIMATIVES);
            $p_array['date_created'] = date('Y-m-d H:i:s', time() - 5);// overwrting time because we'll set it for real below
            $db->insert('people', Arrays::removeFalsey($p_array));
            $person_id = $db->lastInsertId();

            // Attempt to create email record,
            // this may fail (races)

            $email_address = strtolower($from->getEmail());
            list (, $email_domain) = explode('@', $email_address, 2);

            $db->insert('people_emails', array(
                'person_id' => $person_id,
                'email' => $email_address,
                'email_domain' => $email_domain,
                'is_validated' => 1,
                'date_created' => date('Y-m-d H:i:s'),
                'date_validated' => date('Y-m-d H:i:s'),
            ));
            $email_id = $db->lastInsertId();

            $db->update('people', array(
                'primary_email_id' => $email_id
            ), array('id' => $person_id));

            $db->commit();
        } catch (\Exception $e) {

            // We expect/handle a duplicate key error here
            // and re-run ourselves which should fetch the (now available)
            // person record.

            $db->rollback(false);
            if ($this->is_running) {
                $this->is_running = false;
                throw $e;
            }

            $last_e = $e;
        }

        $person = App::getEntityRepository('DeskPRO:Person')->findOneByEmail($from->getEmail(), true);

        if (!$person) {
            if ($this->is_running) {
                if ($last_e) {
                    throw $last_e;
                } else {
                    throw new \RuntimeException();
                }
            }

            $this->is_running = true;
            $person = $this->createPerson($from);
            $this->is_running = false;
        }

        $user_rule_proc = new \Application\DeskPRO\People\UserRuleProcessor(App::getOrm());
        $user_rule_proc->newRegister($person);

        // We need to manually persist the record again
        // so doctrine hooks are run (e.g., to insert into search index)
        $person->date_created = new \DateTime();
        App::$container->getEm()->persist($person);

        return $person;
    }
}
