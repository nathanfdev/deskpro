<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DpTest\DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\PortalBundle\Form\FormFields;
use DpTest\PortalTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class TicketTypeTest extends PortalTestCase
{
    public function setUp()
    {
        $this->installDataSet('fresh', true); // always force a reload to ensure the layout doesn't change
    }

    public function tearDown()
    {
        $this->installDataSet('fresh', true); // clear out the changes we made to the db
    }

    /**
     * This sets up brand and request stacks that some forms need. They are initialized automaitcally in
     * in the HttpKernel requests (listeners) but if we don't use the http kerenel and test it directly, we
     * need to set those up ourselves.
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    protected function prepareEnvForTicketForm()
    {
        $brand       = $this->getRepository(Brand::class)->find(1);
        $brand_stack = $this->get('brand_stack');
        $brand_stack->push($brand);

        $sales_dep = $this->getSalesDep();
        $this->makeCustomLayoutForDep($sales_dep);

        $request = Request::create('/new-ticket');
        $this->get('request_stack')->push($request);
    }

    protected function teardownEnvForTicketForm()
    {
        $this->get('brand_stack')->pop();
        $this->get('request_stack')->pop();
    }

    /**
     * The ticket here does not have a department, so it will use he default layout.
     *
     * This is equilevant to using a department that has a layout_id = null (a null layout means: the default layout)
     */
    public function testInitialStructureOfDefaultLayout()
    {
        $this->prepareEnvForTicketForm();

        $form = $this->createTicketForm(new Ticket(), $this->getNormalPerson());

        $this->assertFields($form, [
            'department',
            'subject',
            'message',
            'user_email',
            'attachments',
            'more_attachments',
            'submit',
            'displayed_fields',
        ]);

        $this->assertDisplayFieldsValue($form, 'department,subject,message,user_email,attach');
        $this->assertRerenderFormDoesNotExist($form);

        $this->teardownEnvForTicketForm();
    }

    /**
     * The "sales" department here is configured in our data set to use a custom layout.
     */
    public function testInitialStructureOfCustomLayout()
    {
        $this->prepareEnvForTicketForm();

        $ticket = new Ticket();
        $ticket->setDepartment($this->getSalesDep());

        $form = $this->createTicketForm($ticket, $this->getNormalPerson());

        $this->assertFields($form, [
            'department',
            'subject',
            'message',
            'user_email',
            'attachments',
            'more_attachments',
            'ticket_field_1',
            'submit',
            'displayed_fields',
        ]);
        $this->assertDisplayFieldsValue($form, 'department,subject,message,user_email,attach,ticket_field_1');
        $this->assertRerenderFormDoesNotExist($form);

        $this->teardownEnvForTicketForm();
    }

    /**
     * The HTML-only form will not update "displayed_fields" or show the new fields when they switch department. Therefore the
     * ticket form will set a flag to "rerender" the form via a hidden form input. This test illustrates that happening.
     *
     * Note that if we were using Javascript to submit this form, it wouldh ave dynamically updated the fields on the screen AND
     * also it will update the "displayed_fields" value to be correct. Doing both of those means we won't ever need to set the
     * re-render flag inside of the form. See testSubmitToADifferentLayoutIsValidWhenDisplayedFieldsIsSet below for an example
     * of submitting to a non-default layout without requiring a re-render.
     */
    public function testSubmitChangingLayoutsCorrectly()
    {
        $this->prepareEnvForTicketForm();

        // setup
        $ticket = new Ticket();
        $person = $this->getNormalPerson();

        $form = $this->createTicketForm($ticket, $person);

        $this->assertFields($form, [
            'department',
            'subject',
            'message',
            'user_email',
            'attachments',
            'more_attachments',
            'submit',
            'displayed_fields',
        ]);

        // test
        $new_dep_id  = $this->getSalesDep()->getId();
        $submit_data = [
            FormFields::DEPARTMENT => $new_dep_id,
            FormFields::SUBJECT    => 'My Test Subject',
            FormFields::MESSAGE    => [
                'message_text'   => 'This is my message, a test message!',
                'message_format' => 'text',
            ],
            FormFields::USER_EMAIL => $person->getPrimaryEmailId(),
            'displayed_fields'     => 'department,subject,message,user_email,attach',
        ];
        $form->submit($submit_data);

        // assert
        $this->assertRerenderFormExists($form);
        $this->assertDisplayFieldsValue($form, 'department,subject,message,user_email,attach,ticket_field_1');
        $this->assertFields($form, [
            'department',
            'subject',
            'message',
            'user_email',
            'attachments',
            'more_attachments',
            'displayed_fields',
            'last_department_id',
            'rerender_form',
            'ticket_field_1',
            'submit',
        ]);
        $this->assertEquals('My Test Subject', $ticket->getSubject());

        $this->teardownEnvForTicketForm();
    }

    /**
     * When you submit a form with "displayed_fields" that matches with the department and the criteria of the form,
     * then all we need is valid input. It will never set the re-render flag in that case. With JS, we manipulate the
     * "displayed_fields" value on the client so that it matches up with that the user is seeing in the dynamic form. In HTML only,
     * the "displayed_fields" is static and so we will mark it with a re-render flag during a change that adds fields.
     *
     * This test shows that passing the correct displayed_fields will not force a re-render. See testSubmitChangingLayoutsCorrectly
     * above for the opposite case (the HTML-only case). As a side-note, the below test is also what happens for a HTML form AFTER
     * an initial re-render, because the static "displayed_fields" value would be up to date with what is displayed during the re-render. The key for re-rendering is the "displayed_fields" value.
     */
    public function testSubmitToADifferentLayoutIsValidWhenDisplayedFieldsIsSet()
    {
        $this->prepareEnvForTicketForm();

        // setup
        $ticket = new Ticket();
        $person = $this->getNormalPerson();

        $form = $this->createTicketForm($ticket, $person);

        $this->assertFields($form, [
            'department',
            'subject',
            'message',
            'user_email',
            'attachments',
            'more_attachments',
            'submit',
            'displayed_fields',
        ]);

        // test
        $new_dep_id  = $this->getSalesDep()->getId();
        $submit_data = [
            FormFields::DEPARTMENT => $new_dep_id,
            FormFields::SUBJECT    => 'Test Subject',
            FormFields::MESSAGE    => [
                'message_text'   => 'This is my message, a test message!',
                'message_format' => 'text',
            ],
            FormFields::USER_EMAIL => $person->getPrimaryEmailId(),
            'ticket_field_1'       => null,
            'displayed_fields'     => 'department,subject,message,user_email,attach,ticket_field_1',
        ];
        $form->submit($submit_data);

        // assert
        $this->assertTrue($form->isValid());
        $this->assertRerenderFormDoesNotExist($form);
        $this->assertEquals('Test Subject', $ticket->getSubject());

        $this->teardownEnvForTicketForm();
    }

    public function testSubmitToADifferentLayoutIsValidWhenDisplayedFieldsIsSetRequest()
    {
        $sales_dep = $this->getSalesDep();
        $this->makeCustomLayoutForDep($sales_dep);

        // setup
        $sales_dep_id = $this->getSalesDep()->getId();
        $client       = $this->getClient();

        // the initial page load is with the default layout,
        // this form will submit with ticket[department]=2 which changes the department, and changes the layout
        // we would expect a re-render here
        $crawler     = $client->request('GET', '/new-ticket');
        $button_node = $crawler->selectButton('ticket_submit');
        $form        = $button_node->form([
            'ticket' => [
                FormFields::DEPARTMENT => $sales_dep_id, // a dep with this default form
                FormFields::SUBJECT    => 'Test Subject',
                FormFields::MESSAGE    => [
                    'message_text'   => 'This is my message, a test message!',
                    'message_format' => 'text',
                ],
                FormFields::USER_EMAIL => [
                    'email' => 'some@test.email',
                ],
                'displayed_fields' => 'department,subject,message,user_email,attach',
            ],
        ]);

        $crawler = $client->submit($form);
        $this->assertRegExp(
            '#/new-ticket#',
            $client->getHistory()->current()->getUri(),
            'the tickt form properly re-renders after a department change with a layout that has additional fields'
        ); // we are still on /new-ticket because we changed dep
        $button_node = $crawler->selectButton('ticket_submit');
        $form        = $button_node->form([
            'ticket' => [
                FormFields::DEPARTMENT => $sales_dep_id,
                FormFields::SUBJECT    => 'Test Subject',
                FormFields::MESSAGE    => [
                    'message_text'   => 'This is my message, a test message!',
                    'message_format' => 'text',
                ],
                FormFields::USER_EMAIL => [
                    'email' => 'some@test.email',
                ],
                'ticket_field_1'   => ['data' => 7], // <---- this is the new field, and we couldn't have submitted this field last time
                'displayed_fields' => 'department,subject,message,user_email,attach,ticket_field_1', // fix
            ],
        ]);

        // now we should get a thank you, because the form is now valid with the new dep:
        // we submitted the new custom field and the displayed_fields now match the expected layout
        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirection());
        $this->assertRegExp('#/thank-you#', $client->getResponse()->headers->get('Location'));
    }

    public function assertFields(FormInterface $form, $expected_fields)
    {
        $form_fields = $this->extractFormfields($form);

        $this->assertEquals($expected_fields, $form_fields, 'form fields are not as expected');
    }

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    protected function extractFormfields(FormInterface $form)
    {
        return array_keys($form->all());
    }

    /**
     * @return mixed
     */
    protected function getBrandSettings()
    {
        return $this->get('brand_stack')->getActive()->getSettings();
    }

    /**
     * @return Person
     */
    protected function getNormalPerson()
    {
        return $this->get('user_details')->getWho('user');
    }

    /**
     * @return Person
     */
    protected function getAdminPerson()
    {
        return $this->get('user_details')->getWho('user');
    }

    /**
     * @return Person
     */
    protected function getAgentPerson()
    {
        return $this->get('user_details')->getWho('user');
    }

    /**
     * @param Ticket $ticket
     * @param Person $person
     *
     * @return \Symfony\Component\Form\Form|FormInterface
     */
    protected function createTicketForm(Ticket $ticket, Person $person)
    {
        // need to include the ticket message as an option if a message is present on form
        $message = new TicketMessage();
        $message->setPerson($person);
        $ticket->addMessage($message);

        $form = $this->getContainer()->get('form.factory')->create('ticket', $ticket, [
            'person'         => $person,
            'ticket_message' => $message,
            'settings'       => $this->getBrandSettings(),
        ]);

        return $form;
    }

    /**
     * COPIED FROM TicketProfileFixture.
     *
     * @param string     $type
     * @param string     $title
     * @param array|null $choices
     *
     * @return CustomDefTicket
     */
    private function createField($type, $title, array $choices = null)
    {
        $options = [];
        switch ($type) {
            case 'text':
                $handler_class = 'Application\DeskPRO\CustomFields\Handler\Text';
                break;
            case 'textarea':
                $handler_class = 'Application\DeskPRO\CustomFields\Handler\Textarea';
                break;
            case 'date':
                $handler_class = 'Application\DeskPRO\CustomFields\Handler\Date';
                break;
            case 'datetime':
                $handler_class = 'Application\DeskPRO\CustomFields\Handler\DateTime';
                break;
            case 'select':
                $handler_class = 'Application\DeskPRO\CustomFields\Handler\Choice';
                break;
            case 'multiselect':
                $handler_class       = 'Application\DeskPRO\CustomFields\Handler\Choice';
                $options['multiple'] = true;
                break;
            case 'checkbox':
                $handler_class       = 'Application\DeskPRO\CustomFields\Handler\Choice';
                $options['multiple'] = true;
                $options['expanded'] = true;
                break;
            case 'radio':
                $handler_class       = 'Application\DeskPRO\CustomFields\Handler\Choice';
                $options['multiple'] = false;
                $options['expanded'] = true;
                break;
            default:
                throw new \InvalidArgumentException();
        }

        $f                  = new CustomDefTicket();
        $f->title           = $title;
        $f->description     = 'A custom '.$f->getWidgetType().' field';
        $f->handler_class   = $handler_class;
        $f->options         = $options;
        $f->is_user_enabled = true;
        $f->is_enabled      = true;

        $this->getEntityManager()->persist($f);
        $this->getEntityManager()->flush();

        if ($handler_class === 'Application\DeskPRO\CustomFields\Handler\Choice' && $choices) {
            foreach ($choices as $c) {
                $this->_createSubOptions($f, null, $c);
            }
        }

        return $f;
    }

    /**
     * COPIED FROM TicketProfileFixture.
     *
     * @param CustomDefTicket      $parent
     * @param CustomDefTicket|null $parent_opt
     * @param array|string         $desc
     *
     * @return CustomDefTicket
     */
    private function _createSubOptions(CustomDefTicket $parent, CustomDefTicket $parent_opt = null, $desc)
    {
        if (is_array($desc)) {
            $title  = $desc[0];
            $others = $desc[1];
        } else {
            $title  = $desc;
            $others = array();
        }

        $opt_f                  = new CustomDefTicket();
        $opt_f->parent          = $parent;
        $opt_f->title           = $title;
        $opt_f->description     = '';
        $opt_f->is_user_enabled = true;
        $opt_f->is_enabled      = true;

        if ($parent_opt) {
            $opt_f->setOption('parent_id', $parent_opt->getId());
        }

        $parent->addChild($opt_f);

        $this->getEntityManager()->persist($opt_f);
        $this->getEntityManager()->flush();

        if ($others) {
            foreach ($others as $sub_title) {
                $this->_createSubOptions($parent, $opt_f, $sub_title);
            }
        }

        return $opt_f;
    }

    /**
     * @param $sales_dep
     */
    protected function makeCustomLayoutForDep($sales_dep)
    {
        $exists = $this->getEntityManager()->getConnection()->fetchColumn('SELECT id FROM ticket_layouts WHERE department_id = '.$sales_dep->getId());

        if (!$exists) {
            $ticket_layout               = new TicketLayout();
            $ticket_layout->is_enabled   = true;
            $ticket_layout->user_layout  = new Layout();
            $ticket_layout->agent_layout = new Layout();
            $ticket_layout->department   = $sales_dep;

            foreach (array(FormFields::DEPARTMENT, FormFields::SUBJECT, FormFields::MESSAGE, FormFields::USER_EMAIL) as $field) {
                $ticket_layout->user_layout->add(new LayoutField($field));
                $ticket_layout->agent_layout->add(new LayoutField($field));
            }

            $ticket_layout->user_layout->add(new LayoutField('attach'));

            $f = $this->createField('radio', 'Reason for Complaint', ['Nuisance', 'Dangerous', 'Smelly', 'Ugly', 'Mean', 'Other']);
            $ticket_layout->user_layout->add(new LayoutField('ticket_field', $f->getId()));

            $this->getEntityManager()->persist($f);
            $this->getEntityManager()->persist($ticket_layout);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Department
     */
    protected function getSalesDep()
    {
        return $this->getRepository(Department::class)->find(2);
    }

    private function assertRerenderFormDoesNotExist(FormInterface $form)
    {
        $this->assertFalse($form->has('rerender_form'), '"rerender_form" field exists, but should not');
    }

    private function assertRerenderFormExists(FormInterface $form)
    {
        $this->assertTrue($form->has('rerender_form'), '"rerender_form" field does not exist, but should');
    }

    /**
     * @param $form
     * @param $text
     */
    protected function assertDisplayFieldsValue($form, $text)
    {
        $fields = $form->get('displayed_fields')->getData();
        $this->assertEquals($text, $fields, 'the displayed fields are set incorrectly');
    }
}
