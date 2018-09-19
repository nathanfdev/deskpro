<?php

namespace DpBehat\Data;

use Application\DeskPRO\Entity\LabelPerson;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\Usergroup;
use DpBehat\BaseContext;
use DpBehat\Data\Factory\SimpleFactory;

/**
 * Class PeopleContext.
 */
class PeopleContext extends BaseContext
{
    /**
     * @Given default everyone user group exists
     *
     * @return Usergroup
     */
    public function everyoneGroupExists()
    {
        return $this->usergroupExists(Usergroup::EVERYONE);
    }

    /**
     * @Given default registered user group exists
     *
     * @return Usergroup
     */
    public function registeredGroupExists()
    {
        return $this->usergroupExists(Usergroup::REGISTERED);
    }

    /**
     * @Given default all safe permissions agent group exists
     *
     * @return Usergroup
     */
    public function agentAllSafePermGroupExists()
    {
        return $this->agentGroupExists(Usergroup::AGENT_ALL_SAFE_PERM);
    }

    /**
     * @Given default all permissions agent group exists
     *
     * @return Usergroup
     */
    public function agentAllPermGroupExists()
    {
        return $this->agentGroupExists(Usergroup::AGENT_ALL_PERM);
    }

    /**
     * @Given :name agent group exits
     *
     * @param string $name
     *
     * @return Usergroup
     */
    public function agentGroupExists($name)
    {
        return $this->createUsergroup($name, true);
    }

    /**
     * @Given :name user group exists
     *
     * @param string $name
     *
     * @return Usergroup
     */
    public function usergroupExists($name)
    {
        return $this->createUsergroup($name, false);
    }

    /**
     * @Given :name agent group exists
     *
     * @param string $name
     *
     * @return Usergroup
     */
    public function agenntgroupExists($name)
    {
        return $this->createUsergroup($name, true);
    }

    /**
     * @Given :role person exists
     *
     * @param $role
     *
     * @throws \Exception
     *
     * @return Person
     */
    public function personByRoleExists($role)
    {
        $email = "$role@deskpro.dev";

        switch ($role) {
            case 'admin':
                return $this->adminExists($email);
            case 'agent':
                return $this->agentExists($email);
            case 'user':
                return $this->userExists($email);
            default:
                throw new \Exception("Unknown person role $role");
        }
    }

    /**
     * @Given :email admin exists
     * @Given :email admin exists with :super key
     * @Given an admin with ":email" email exists
     *
     * @param string $email
     * @param string $super
     *
     * @throws \Exception
     *
     * @return Person
     */
    public function adminExists($email, $super = false)
    {
        $person = $this->findPersonByEmail($email);
        if ($person) {
            if (!$person->canAdmin()) {
                throw new \Exception("Person $email already exists but not admin");
            }
        } else {
            /** @var Person $person */
            $person = SimpleFactory::create(Person::class, [
                'first_name'   => 'Admin',
                'last_name'    => 'Admin',
                'is_user'      => true,
                'is_confirmed' => true,
                'is_agent'     => true,
                'can_agent'    => true,
                'can_admin'    => true,
                'password'     => 'password',
            ]);

            $person->setEmail($email, true);
        }

        $person->addUsergroup($this->everyoneGroupExists());
        $person->addUsergroup($this->registeredGroupExists());
        $person->addUsergroup($this->agentAllSafePermGroupExists());
        $person->addUsergroup($this->agentAllPermGroupExists());

        $this->persistAndFlush($person);

        DataContext::setReference($email, $person);
        if ($email === 'admin@deskpro.dev') {
            DataContext::setReference('admin', $person);
        }

        return $person;
    }

    /**
     * @Given :email agent exists
     * @Given an agent with :email email exists
     *
     * @param string $email
     *
     * @throws \Exception
     *
     * @return Person
     */
    public function agentExists($email)
    {
        $person = $this->findPersonByEmail($email);
        if ($person) {
            if (!$person->isAgent()) {
                throw new \Exception("Person $email already exists but not agent");
            }
        } else {
            /** @var Person $person */
            $person = SimpleFactory::create(Person::class, [
                'first_name'   => 'Agent',
                'last_name'    => 'Agent',
                'is_user'      => true,
                'is_confirmed' => true,
                'is_agent'     => true,
                'can_agent'    => true,
                'can_admin'    => false,
                'password'     => 'password',
            ]);

            $person->setEmail($email, true);
        }

        $person->addUsergroup($this->everyoneGroupExists());
        $person->addUsergroup($this->registeredGroupExists());
        $person->addUsergroup($this->agentAllSafePermGroupExists());

        $this->persistAndFlush($person);

        DataContext::setReference($email, $person);
        if ($email === 'agent@deskpro.dev') {
            DataContext::setReference('agent', $person);
        }

        return $person;
    }

    /**
     * @Given :email user exists
     * @Given a user with :email email exists
     *
     * @param string $email
     *
     * @throws \Exception
     *
     * @return Person
     */
    public function userExists($email)
    {
        $person = $this->findPersonByEmail($email);
        if ($person) {
            if ($person->isAgent()) {
                throw new \Exception("Person $email already exists but it's an agent");
            }
        } else {
            /* @var Person $person */
            $person = SimpleFactory::create(Person::class, [
                'first_name'   => 'User',
                'last_name'    => 'User',
                'is_user'      => true,
                'is_confirmed' => true,
                'is_agent'     => false,
                'can_agent'    => false,
                'can_admin'    => false,
                'password'     => 'password',
            ]);

            $person->setEmail($email, true);
        }

        $person->addUsergroup($this->everyoneGroupExists());
        $person->addUsergroup($this->registeredGroupExists());

        $this->persistAndFlush($person);

        DataContext::setReference($email, $person);
        if ($email === 'user@deskpro.dev') {
            DataContext::setReference('user', $person);
        }

        return $person;
    }

    /**
     * @Given agent and user exist
     */
    public function agentAndUserExist()
    {
        $this->userExists('user@deskpro.dev');
        $this->agentExists('agent@deskpro.dev');
    }

    /**
     * @param string $who
     * @param string $label
     *
     * @throws \Exception
     *
     * @Given I mark :who user with :label label
     */
    public function iMarkUserWithLabel($who, $label)
    {
        /** @var Person $person */
        $person = DataContext::getReference($who);
        $label  = $this->findOrCreateLabel($label, $person);
        $person->addLabel($label);
        $this->persistAndFlush($person);
    }

    /**
     * @param string $label
     * @param Person $person
     *
     * @return LabelPerson
     */
    private function findOrCreateLabel($label, Person $person)
    {
        $repo = $this->em()->getRepository(LabelPerson::class);
        if (!$labelObject = $repo->findOneBy(['label' => $label, 'person' => $person])) {
            $labelObject = new LabelPerson();
            $labelObject->setLabel($label);
            $labelObject->person = $person;
            $this->persistAndFlush($labelObject);
        }

        return $labelObject;
    }

    /**
     * @param string $name
     * @param bool   $isAgent
     *
     * @return Usergroup
     */
    private function createUsergroup($name, $isAgent)
    {
        /** @var Usergroup $group */
        $group = $this->repository(Usergroup::class)->findOneBy(['sys_name' => $name]);
        if (!$group) {
            $group = SimpleFactory::create(Usergroup::class, [
                'title'          => $name,
                'note'           => $name,
                'sys_name'       => $name,
                'is_agent_group' => $isAgent,
                'is_enabled'     => 1,
            ]);

            $this->persistAndFlush($group);
        }

        DataContext::setReference("{$name}_group", $group);

        return $group;
    }

    /**
     * @param string $email
     *
     * @return Person|null
     */
    public function findPersonByEmail($email)
    {
        $email = $this->repository(PersonEmail::class)->findOneBy(['email' => $email]);

        return $email ? $email->getPerson() : null;
    }
}
