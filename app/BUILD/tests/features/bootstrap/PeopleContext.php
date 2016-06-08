<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DpBehat;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use DpBehat\Data\DataContext;
use DpBehat\Data\Factory\PersonFactories;

/**
 * Class PeopleContext.
 */
class PeopleContext extends BaseContext
{
    /**
     * @Given ":email" agent exists
     * @Given an agent with ":email" email exists
     */
    public function agentExists($email)
    {
        if (!DataContext::hasReference($email)) {
            DataContext::setReference($email, $person = PersonFactories::create('agent', compact('email')));
            $this->persistAndFlush($person);
        }
    }

    /**
     * @Given ":email" user exists
     * @Given a user with ":email" email exists
     */
    public function userExists($email)
    {
        if (!DataContext::hasReference($email)) {
            DataContext::setReference($email, $person = PersonFactories::create('user', compact('email')));
            $this->persistAndFlush($person);
        }
    }

    /**
     * @Given agent@deskpro.com and user@deskpro.com exist
     */
    public function agentAndUserExist()
    {
        if (!DataContext::hasReference('agent')) {
            $email = 'agent@deskpro.com';
            if (!$agent = $this->findPersonByEmail($email)) {
                $agent = PersonFactories::create('agent', compact('email'));
                $this->persistAndFlush($agent);
            }
            DataContext::setReference('agent', $agent);
        }
        if (!DataContext::hasReference('user')) {
            $email = 'user@deskpro.com';
            if (!$user = $this->findPersonByEmail($email)) {
                $user = PersonFactories::create('user', compact('email'));
                $this->persistAndFlush($user);
            }
            DataContext::setReference('user', $user);
        }
    }

    /**
     * @param string $email
     *
     * @return Person|null
     */
    private function findPersonByEmail($email)
    {
        $email = $this->repository(PersonEmail::class)->findOneBy(compact('email'));

        return $email ? $email->getPerson() : null;
    }
}
