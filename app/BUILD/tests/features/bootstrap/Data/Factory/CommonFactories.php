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

namespace DpBehat\Data\Factory;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DpBehat\Data\DataContext;

/**
 * Class CommonFactories.
 */
class CommonFactories
{
    /**
     * @param string $role
     * @param array  $data
     *
     * @return Person
     */
    public static function person($role, array $data = [])
    {
        $data['password'] = 'password';
        if ($role === 'agent') {
            $data['is_agent'] = 1;
        } elseif ($role === 'admin') {
            $data['is_agent']  = 1;
            $data['can_admin'] = 1;
        }

        $person = new Person();

        return SimpleFactory::provide($person, $data);
    }

    /**
     * @param array $data
     *
     * @return Ticket
     */
    public static function ticket(array $data = [])
    {
        $ticket = new Ticket();
        $ticket->disableAutoTicketProcess();

        if (!array_key_exists('agent', $data)) {
            $me = DataContext::getReference('me', false);
            if ($me && $me->isAgent()) {
                $data['agent'] = $me;
            }
        }

        Helper::pick($data, 'subject', $ticket, 'setSubject', uniqid('Ticket_'));

        return SimpleFactory::provide($ticket, $data);
    }

    /**
     * @param array $data
     *
     * @return Department
     */
    public static function department(array $data = [])
    {
        $department = new Department();
        Helper::pick($data, 'title', $department, 'setRealTitle', uniqid('Department_'));

        return SimpleFactory::provide($department, $data);
    }

    /**
     * @param string $type
     * @param array  $data
     *
     * @throws \Exception
     *
     * @return CustomDefAbstract
     */
    public static function customDef($type, array $data = [])
    {
        $types = [
            'ticket'       => CustomDefTicket::class,
            'organization' => CustomDefOrganization::class,
            'person'       => CustomDefPerson::class,
            'conversation' => CustomDefChat::class,
            'feedback'     => CustomDefFeedback::class,
        ];

        $typeToHandler = [
            ''               => null,
            'text'           => CustomDefAbstract::HANDLER_CLASS_TEXT,
            'textarea'       => CustomDefAbstract::HANDLER_CLASS_TEXTAREA,
            'date'           => CustomDefAbstract::HANDLER_CLASS_DATE,
            'datetime'       => CustomDefAbstract::HANDLER_CLASS_DATETIME,
            'checkbox_group' => CustomDefAbstract::HANDLER_CLASS_CHOICE,
            'radio_group'    => CustomDefAbstract::HANDLER_CLASS_CHOICE,
            'single_choice'  => CustomDefAbstract::HANDLER_CLASS_CHOICE,
            'multi_choice'   => CustomDefAbstract::HANDLER_CLASS_CHOICE,
            'toggle'         => CustomDefAbstract::HANDLER_CLASS_TOGGLE,
            'hidden'         => CustomDefAbstract::HANDLER_CLASS_HIDDEN,
        ];

        $def = new $types[$type]();

        // Type to handler class
        if (!array_key_exists('type', $data)) {
            $data['type'] = '';
        }
        if (!array_key_exists($data['type'], $typeToHandler)) {
            throw new \Exception("Unknown handler class '{$data['type']}''");
        }

        $data['handler_class'] = $typeToHandler[$data['type']];

        if (in_array($data['type'], ['checkbox_group', 'multi_choice'])) {
            $data['options']['multiple'] = true;
        }
        if (in_array($data['type'], ['checkbox_group', 'radio_group'])) {
            $data['options']['expanded'] = true;
        }

        unset($data['type']);

        Helper::pick($data, 'handler_class', $def, 'setHandlerClass');

        // Provide rest of the $data properties
        return SimpleFactory::provide($def, $data);
    }

    /**
     * @param array $data
     *
     * @return Task
     */
    public static function task(array $data = [])
    {
        $task = new Task();
        Helper::pick($data, 'title', $task, 'setTitle', uniqid('Task_'));

        return SimpleFactory::provide($task, $data);
    }

    /**
     * @param array $data
     *
     * @return Product
     */
    public static function product(array $data)
    {
        $product = new Product();
        Helper::pick($data, 'title', $product, 'setTitle', uniqid('Product_'));

        return SimpleFactory::provide($product, $data);
    }

    /**
     * @param array $data
     *
     * @return Product
     */
    public static function sla(array $data)
    {
        $sla = new Sla();
        if (!array_key_exists('type', $data)) {
            $data['type'] = 'first_response';
        }

        Helper::pick($data, 'title', $sla, 'setTitle', uniqid('Sla_'));
        Helper::pick($data, 'type', $sla, 'setSlaType', uniqid('type_'));

        return SimpleFactory::provide($sla, $data);
    }

    /**
     * @param array $data
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return object
     */
    public static function agentTeam(array $data)
    {
        $team = new AgentTeam();
        if (isset($data['members'])) {
            foreach ($data['members'] as $member) {
                $team->addPerson($member);
            }
            unset($data['members']);
        }

        return SimpleFactory::provide($team, $data);
    }
}
