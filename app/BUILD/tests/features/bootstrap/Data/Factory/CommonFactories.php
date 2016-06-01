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

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefOrganization;
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
     * @param array  $data
     *
     * @return Ticket
     */
    public static function ticket(array $data = [])
    {
        $ticket = new Ticket();
        $ticket->disableAutoTicketProcess();
        array_key_exists('agent', $data) or $data['agent'] = DataContext::getReference('me', false);
        Helper::pick($data, 'subject', $ticket, 'setSubject', uniqid('Ticket_'));

        SimpleFactory::provide($ticket, $data);

        return $ticket;
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

        SimpleFactory::provide($department, $data);

        return $department;
    }

    /**
     * @param string $type
     * @param array $data
     *
     * @return CustomDefAbstract
     */
    public static function customDef($type, array $data = [])
    {
        $type = [
            'ticket' => CustomDefTicket::class,
            'organization' => CustomDefOrganization::class,
        ][$type];
        $def = new $type;

        // Type to handler class
        array_key_exists('type', $data) or $data['type'] = '';
        $typeToHandler                                   = [
            ''              => null,
            'text'          => 'Application\DeskPRO\CustomFields\Handler\Text',
            'textarea'      => 'Application\DeskPRO\CustomFields\Handler\Textarea',
            'date'          => 'Application\DeskPRO\CustomFields\Handler\Date',
            'datetime'      => 'Application\DeskPRO\CustomFields\Handler\DateTime',
            'multi_choice'  => 'Application\DeskPRO\CustomFields\Handler\Choice',
            'single_choice' => 'Application\DeskPRO\CustomFields\Handler\Choice',
        ];
        $data['handler_class']                               = $typeToHandler[$data['type']];
        $data['type'] !== 'multi_choice' or $data['options'] = ['multiple' => true, 'expanded' => true];
        unset($data['type']);
        Helper::pick($data, 'handler_class', $def, 'setHandlerClass');

        // Provide rest of the $data properties
        SimpleFactory::provide($def, $data);

        return $def;
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

        SimpleFactory::provide($task, $data);

        return $task;
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

        SimpleFactory::provide($product, $data);

        return $product;
    }

    /**
     * @param array $data
     *
     * @return Product
     */
    public static function sla(array $data)
    {
        $sla                                             = new Sla();
        array_key_exists('type', $data) or $data['type'] = 'first_response';
        Helper::pick($data, 'title', $sla, 'setTitle', uniqid('Sla_'));
        Helper::pick($data, 'type', $sla, 'setSlaType', uniqid('type_'));

        SimpleFactory::provide($sla, $data);

        return $sla;
    }
}
