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

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Application\DeskPRO\Entity\CustomDataTicket;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use DpTest\PortalTestCase;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class TicketLayoutValidatorTest.
 */
class TicketLayoutValidatorTest extends PortalTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->installDataSet('empty', true);
    }

    public function testValidateDateFields()
    {
        $ticket_layout               = new TicketLayout();
        $ticket_layout->agent_layout = new Layout();
        $ticket_layout->agent_layout->add(new LayoutField(FormFields::TICKET_FIELD, 1));
        $ticket_layout->agent_layout->add(new LayoutField(FormFields::TICKET_FIELD, 2));

        $custom_field_1 = new CustomDefTicket();
        $custom_field_1->setHandlerClass(CustomDefAbstract::HANDLER_CLASS_DATE);
        $custom_field_1->setOptions([
            'agent_required'   => 1,
            'date_valid_type'  => 'date',
            'date_valid_date1' => '2016-03-22',
            'date_valid_date2' => '2016-03-26',
        ]);

        $custom_field_2 = new CustomDefTicket();
        $custom_field_2->setHandlerClass(CustomDefAbstract::HANDLER_CLASS_DATETIME);
        $custom_field_2->setOptions([
            'date_valid_type'   => 'range',
            'date_valid_range1' => '5',
            'date_valid_range2' => '5',
            'date_valid_dow'    => [0, 1, 2, 3, 5, 6],
        ]);

        $em = $this->getEntityManager();
        $em->persist($ticket_layout);
        $em->persist($custom_field_1);
        $em->persist($custom_field_2);

        $em->flush();

        // Test required
        $ticket = new Ticket();
        $errors = $this->validate($ticket);
        $this->assertCount(1, $errors);

        $this->assertEquals('This value should not be blank.', $errors[0]->getMessage());
        $this->assertEquals('custom_data[1]', $errors[0]->getPropertyPath());

        // Test invalid format
        $custom_data_1 = new CustomDataTicket();
        $custom_data_1->setField($custom_field_1);
        $custom_data_1->setRootField($custom_field_1);
        $custom_data_1->setValue('not_date');

        $custom_data_2 = new CustomDataTicket();
        $custom_data_2->setField($custom_field_2);
        $custom_data_2->setRootField($custom_field_2);
        $custom_data_2->setValue('not_date_time');

        $ticket->addCustomData($custom_data_1);
        $ticket->addCustomData($custom_data_2);

        $errors = $this->validate($ticket);
        $this->assertCount(5, $errors);

        $this->assertEquals('This value is not a valid date.', $errors[0]->getMessage());
        $this->assertEquals('custom_data[1]', $errors[0]->getPropertyPath());
        $this->assertEquals('This value should be less than or equal to {{ compared_value }}.', $errors[1]->getMessage());
        $this->assertEquals('custom_data[1]', $errors[1]->getPropertyPath());

        $this->assertEquals('This value is not a valid datetime.', $errors[2]->getMessage());
        $this->assertEquals('custom_data[2]', $errors[2]->getPropertyPath());
        $this->assertEquals('This value should be less than or equal to {{ compared_value }}.', $errors[3]->getMessage());
        $this->assertEquals('custom_data[2]', $errors[3]->getPropertyPath());
        $this->assertEquals('The value you selected is not a valid choice.', $errors[4]->getMessage());
        $this->assertEquals('custom_data[2]', $errors[4]->getPropertyPath());

        // Test ranges
        $datetime = new \DateTime('-6 days');
        $datetime = $datetime->format('c');

        $custom_data_1->setValue('2016-03-21');
        $custom_data_2->setValue($datetime);

        $errors = $this->validate($ticket);
        $this->assertCount(2, $errors);

        $this->assertEquals('This value should be greater than or equal to {{ compared_value }}.', $errors[0]->getMessage());
        $this->assertEquals('custom_data[1]', $errors[0]->getPropertyPath());
        $this->assertEquals('This value should be greater than or equal to {{ compared_value }}.', $errors[1]->getMessage());
        $this->assertEquals('custom_data[2]', $errors[1]->getPropertyPath());

        $datetime = new \DateTime('+6 days');
        $datetime = $datetime->format('c');

        $custom_data_1->setValue('2016-03-27');
        $custom_data_2->setValue($datetime);

        $errors = $this->validate($ticket);
        $this->assertCount(2, $errors);

        $this->assertEquals('This value should be less than or equal to {{ compared_value }}.', $errors[0]->getMessage());
        $this->assertEquals('custom_data[1]', $errors[0]->getPropertyPath());
        $this->assertEquals('This value should be less than or equal to {{ compared_value }}.', $errors[1]->getMessage());
        $this->assertEquals('custom_data[2]', $errors[1]->getPropertyPath());

        // Test day of week
        $custom_data_1->setValue('2016-03-22');
        $custom_data_2->setValue('2016-03-25');

        $errors = $this->validate($ticket);
        $this->assertCount(1, $errors);

        $this->assertEquals('The value you selected is not a valid choice.', $errors[0]->getMessage());
        $this->assertEquals('custom_data[2]', $errors[0]->getPropertyPath());

        // Test valid values
        $custom_data_1->setValue('2016-03-22');
        $custom_data_2->setValue('2016-03-24 15:00:00');

        $errors = $this->validate($ticket);
        $this->assertCount(0, $errors);
    }

    public function testValidateTextFields()
    {
        $ticket_layout               = new TicketLayout();
        $ticket_layout->agent_layout = new Layout();
        $ticket_layout->agent_layout->add(new LayoutField(FormFields::TICKET_FIELD, 1));
        $ticket_layout->agent_layout->add(new LayoutField(FormFields::TICKET_FIELD, 2));
        $ticket_layout->agent_layout->add(new LayoutField(FormFields::TICKET_FIELD, 3));

        $custom_field_1 = new CustomDefTicket();
        $custom_field_1->setHandlerClass(CustomDefAbstract::HANDLER_CLASS_TEXT);
        $custom_field_1->setOptions([
            'agent_required'   => 1,
            'agent_min_length' => 5,
            'agent_max_length' => 10,
        ]);

        $custom_field_2 = new CustomDefTicket();
        $custom_field_2->setHandlerClass(CustomDefAbstract::HANDLER_CLASS_TEXTAREA);
        $custom_field_2->setOptions([
            'agent_regex' => '^[a-d]+$',
        ]);

        $custom_field_3 = new CustomDefTicket();
        $custom_field_3->setHandlerClass(CustomDefAbstract::HANDLER_CLASS_HIDDEN);
        $custom_field_3->setOptions([
            'agent_required' => 1,
        ]);

        $em = $this->getEntityManager();
        $em->persist($ticket_layout);
        $em->persist($custom_field_1);
        $em->persist($custom_field_2);
        $em->persist($custom_field_3);

        $em->flush();

        $ticket = new Ticket();

        // Test required
        $errors = $this->validate($ticket);
        $this->assertCount(2, $errors);

        $this->assertEquals('This value should not be blank.', $errors[0]->getMessage());
        $this->assertEquals('custom_data[1]', $errors[0]->getPropertyPath());

        $this->assertEquals('This value should not be blank.', $errors[1]->getMessage());
        $this->assertEquals('custom_data[3]', $errors[1]->getPropertyPath());

        $custom_data_1 = new CustomDataTicket();
        $custom_data_1->setField($custom_field_1);
        $custom_data_1->setRootField($custom_field_1);
        $custom_data_1->setValue('');

        $custom_data_2 = new CustomDataTicket();
        $custom_data_2->setField($custom_field_2);
        $custom_data_2->setRootField($custom_field_2);
        $custom_data_2->setValue('');

        $custom_data_3 = new CustomDataTicket();
        $custom_data_3->setField($custom_field_3);
        $custom_data_3->setRootField($custom_field_3);
        $custom_data_3->setValue('');

        $ticket->addCustomData($custom_data_1);
        $ticket->addCustomData($custom_data_2);
        $ticket->addCustomData($custom_data_3);

        $errors = $this->validate($ticket);
        $this->assertCount(2, $errors);

        $this->assertEquals('This value should not be blank.', $errors[0]->getMessage());
        $this->assertEquals('custom_data[1]', $errors[0]->getPropertyPath());

        $this->assertEquals('This value should not be blank.', $errors[1]->getMessage());
        $this->assertEquals('custom_data[3]', $errors[1]->getPropertyPath());

        // Test length
        $custom_data_1->setInput('val');
        $custom_data_3->setInput('val');

        $errors = $this->validate($ticket);
        $this->assertCount(1, $errors);

        $this->assertEquals('This value is too short. It should have {{ limit }} character or more.|This value is too short. It should have {{ limit }} characters or more.', $errors[0]->getMessage());
        $this->assertEquals('custom_data[1]', $errors[0]->getPropertyPath());

        $custom_data_1->setInput('valvalvalvalvalval');

        $errors = $this->validate($ticket);
        $this->assertCount(1, $errors);

        $this->assertEquals('This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.', $errors[0]->getMessage());
        $this->assertEquals('custom_data[1]', $errors[0]->getPropertyPath());

        // Test regex
        $custom_data_1->setInput('some val');
        $custom_data_2->setInput('some val');
        $custom_data_3->setInput('some val');

        $errors = $this->validate($ticket);
        $this->assertCount(1, $errors);

        $this->assertEquals('This value is not valid.', $errors[0]->getMessage());
        $this->assertEquals('custom_data[2]', $errors[0]->getPropertyPath());

        // Test valid values
        $custom_data_2->setInput('abc');

        $errors = $this->validate($ticket);
        $this->assertCount(0, $errors);
    }

    /**
     * @param Ticket $ticket
     *
     * @return ConstraintViolation[]
     */
    private function validate(Ticket $ticket)
    {
        $validator  = $this->get('validator');
        $constraint = new AppAssert\Ticket\TicketLayout([
            'context' => 'agent',
        ]);

        return $validator->validate($ticket, $constraint);
    }
}
