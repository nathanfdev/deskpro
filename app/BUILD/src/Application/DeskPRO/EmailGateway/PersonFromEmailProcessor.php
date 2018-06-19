<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Component\Util\ListUtils;
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
        if (!$person['first_name'] and !$person['last_name']) {
            if ($from->getName()) {
                $person['name'] = $from->getName();
                App::getOrm()->persist($person);
            }
        }
    }

    /**
     * Finds a person based on the From in the email address.
     *
     * @param EmailAddress $from
     *
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

        return;
    }

    /**
     * Finds a person based on the From in the email address.
     *
     * @param string      $email_address The email address as a string
     * @param string|null $name
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    public function findPersonByEmailAddress($email_address, $name = null)
    {
        $email        = new EmailAddress();
        $email->email = $email_address;

        if ($name) {
            $email->name      = $name;
            $email->name_utf8 = $name;
        }

        return $this->findPerson($email);
    }

    /**
     * @param $email_address
     * @param null $name
     *
     * @return Entity\Person
     */
    public function createPersonByEmailAddress($email_address, $name = null)
    {
        $email        = new EmailAddress();
        $email->email = $email_address;

        if ($name) {
            $email->name      = $name;
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
     * @param EmailAddress $from
     *
     * @throws \Exception
     *
     * @return Entity\Person
     */
    public function createPerson(EmailAddress $from)
    {
        $person = App::getEntityRepository(Person::class)->findOneByEmail($from->getEmail(), true);
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
            $tmp_person = Entity\Person::newContactPerson([
                'creation_system' => $this->creation_system,
                'name'            => $from->getNameUtf8() ?: '',
            ]);

            // Create new person record (no chance of conflicts here)
            $p_array                 = $tmp_person->toArray(Entity\Person::TOARRAY_ONLY_PRIMATIVES);
            $p_array['date_created'] = date('Y-m-d H:i:s', time() - 5); // overwrting time because we'll set it for real below
            $db->insert('people', Arrays::removeFalsey($p_array));
            $personId = $db->lastInsertId();

            // Since we are 'manually' inserting the user here, Person->isNew will think
            // it already existed, so we need this hack to override it
            if (!isset($GLOBALS['DP_CREATED_PEOPLE_IDS'])) {
                $GLOBALS['DP_CREATED_PEOPLE_IDS'] = [];
            }
            $GLOBALS['DP_CREATED_PEOPLE_IDS'][$personId] = $personId;

            // Attempt to create email record,
            // this may fail (races)

            $emailAddress        = strtolower($from->getEmail());
            list(, $emailDomain) = explode('@', $emailAddress, 2);

            $db->insert('people_emails', [
                'person_id'    => $personId,
                'email'        => $emailAddress,
                'email_domain' => $emailDomain,
                'date_created' => date('Y-m-d H:i:s'),
            ]);
            $emailId = $db->lastInsertId();

            $db->update('people', [
                'primary_email_id' => $emailId,
            ], ['id' => $personId]);

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

        $person = App::getEntityRepository(Person::class)->findOneByEmail($from->getEmail(), true);

        if (!$person) {
            if ($this->is_running) {
                if ($last_e) {
                    throw $last_e;
                } else {
                    throw new \RuntimeException();
                }
            }

            $this->is_running = true;
            $person           = $this->createPerson($from);
            $this->is_running = false;
        }

        $userRuleProcessor = new \Application\DeskPRO\People\UserRuleProcessor(App::getOrm());
        $userRuleProcessor->newRegister($person);

        // We need to manually persist the record again
        // so doctrine hooks are run (e.g., to insert into search index)
        $person->date_created = new \DateTime();
        App::$container->getEm()->persist($person);

        return $person;
    }

    /**
     * Is Account open for registration and has Brands with UserSources enabled for registration.
     *
     * @param EmailAccount $account
     *
     * @return bool
     */
    public function canAssociatePersonWithAccountBrands(EmailAccount $account)
    {
        return (bool) $this->getFirstAccountBrandEnabledForRegistration($account, 'user');
    }

    /**
     * @param Person       $person
     * @param EmailAccount $account
     *
     * @return bool
     */
    public function isPersonAssociatedWithAccountBrands(EmailAccount $account, Person $person)
    {
        return $person->hasOneOfTheBrands($this->getAccountBrands($account));
    }

    /**
     * @param EmailAccount $account
     * @param Person       $person
     * @param $forceRegEnabled
     *
     * @return Brand | boolean
     */
    public function associatePersonWithAccountBrand(EmailAccount $account, Person $person, $forceRegEnabled = true)
    {
        $brand = $this->getFirstAccountBrandEnabledForRegistration($account, $person->is_agent ? 'agent' : 'user');

        if (!$brand && $forceRegEnabled) {
            return false;
        }

        if (!$brand) {
            $brand = ListUtils::first($this->getAccountBrands($account));
        }

        $db = App::getDb();
        $db->beginTransaction();
        try {
            $db->insert('person_to_brand', [
                'person_id' => $person->getId(),
                'brand_id'  => $brand->getId(),
            ]);
            $db->commit();
        } catch (\Exception $e) {
            // We expect/handle a duplicate key error here
            $db->rollback(false);
        }

        $person->addBrand($brand);
        App::$container->getEm()->persist($person);

        return $brand;
    }

    /**
     * Try to find first Account Brand with `reg_enabled` UserSource
     * Return found Brand or false.
     *
     * @param EmailAccount $account
     * @param string       $interface 'user'|'agent'
     *
     * @return bool|Brand
     */
    protected function getFirstAccountBrandEnabledForRegistration(EmailAccount $account, $interface)
    {
        /** @var \Application\DeskPRO\Usersource\UsersourceManager $usersourceManager */
        $usersourceManager = App::$container->getSystemService('usersource_manager');

        // find first Brand with `reg_enabled` Usersource
        foreach ($this->getAccountBrands($account) as $brand) {
            if (
                $usersourceManager
                    ->getAll()
                    ->forInterface($interface)
                    ->forBrand($brand)
                    ->withNoApp()
                    ->mustHaveRegEnabled()
                    ->getFirstOrNull()
            ) {
                return $brand;
            }
        }

        return false;
    }

    /**
     * @param EmailAccount $account
     *
     * @return Brand[]
     */
    protected function getAccountBrands(EmailAccount $account)
    {
        return $account->isAllBrands() ? App::getEntityRepository(Brand::class)->findAll() : $account->getBrands();
    }
}
