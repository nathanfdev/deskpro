<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\TicketLayout\LayoutDisplay;
use Application\DeskPRO\Tickets\SnippetFormatter;
use DeskPRO\Bundle\AppBundle\Entity\SnippetTranslation;
use Doctrine\ORM\EntityManager;
use Orb\Util\Strings;

class NewTicket
{
    /** @var \Application\AgentBundle\Form\Model\NewTicketPerson */
    public $person;

    /** @var string */
    public $subject;
    /** @var string */
    public $notify_template = '';
    /** @var string */
    public $message;
    /** @var bool */
    public $is_html_reply;
    /** @var int */
    public $brand_id;
    /** @var int */
    public $department_id;
    /** @var string */
    public $status;
    /** @var int */
    public $agent_id;
    /** @var int */
    public $agent_team_id;
    /** @var int */
    public $category_id = 0;
    /** @var int */
    public $priority_id = 0;
    /** @var int */
    public $workflow_id = 0;
    /** @var int */
    public $product_id = 0;

    /** @var string */
    public $billing_type = '';
    /** @var int */
    public $billing_amount = 0;
    /** @var int */
    public $billing_hours = 0;
    /** @var int */
    public $billing_minutes = 0;
    /** @var int */
    public $billing_seconds = 0;
    /** @var string */
    public $billing_comment = '';

    /** @var array */
    public $add_cc_person = [];
    /** @var array */
    public $add_cc_newpeople = [];
    /** @var array */
    public $add_cc_newperson = [];
    /** @var array */
    public $attach = [];
    /** @var array */
    public $ticket_fields = [];
    /** @var int */
    public $organization_id;

    /**
     * @var array
     */
    public $add_followers = [];

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em;

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    protected $_ticket;

    /**
     * @var \Application\DeskPRO\Tickets\TicketManager
     */
    protected $_ticket_manager;

    /**
     * @var callable
     */
    protected $_pre_save_callback;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $_person_context;

    /**
     * @var LayoutDisplay
     */
    protected $layout;

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    public $exist_ticket;

    /**
     * @var array
     */
    protected $_blob_inline_ids = [];

    /**
     * @var bool
     */
    public $suppress_user_notify = false;

    /**
     * @var array
     */
    public $custom_person_fields      = [];
    public $post_custom_person_fields = [];

    /**
     * @var array
     */
    public $custom_org_fields      = [];
    public $post_custom_org_fields = [];

    /**
     * @var bool
     */
    public $is_note = false;

    public function __construct(EntityManager $em, Person $person_context)
    {
        $this->_em             = $em;
        $this->_person_context = $person_context;

        // TODO
        $this->_ticket_manager = App::$container->getTicketManager();

        $this->person = new NewTicketPerson();
    }

    /**
     * @param LayoutDisplay $layout
     */
    public function setLayout(LayoutDisplay $layout)
    {
        $this->layout = $layout;
    }

    /**
     * @return Ticket
     */
    public function getMockTicket()
    {
        $t = new Ticket();
        $p = new Person();
        $o = new Organization();
        $t->_setNoPersist();

        if ($this->brand_id) {
            $t->setBrandId($this->brand_id);
        }
        if ($this->department_id) {
            $t->setDepartmentId($this->department_id);
        }
        if ($this->workflow_id) {
            $t->setWorkflowId($this->workflow_id);
        }
        if ($this->product_id) {
            $t->setProductId($this->product_id);
        }
        if ($this->priority_id) {
            $t->setPriorityId($this->priority_id);
        }
        if ($this->category_id) {
            $t->setCategoryId($this->category_id);
        }
        if ($this->status) {
            $t->setStatus($this->status);
        }

        $t->person       = $p;
        $p->organization = $o;

        App::$container->getTicketFieldManager()->setFormToObject($this->ticket_fields, $t);
        App::$container->getPersonFieldManager()->setFormToObject($this->custom_person_fields, $p);
        App::$container->getOrgFieldManager()->setFormToObject($this->custom_org_fields, $o);

        return $t;
    }

    public function setValuesFromTicket(Ticket $ticket = null, Person $person = null, Organization $org = null)
    {
        if ($ticket) {
            $this->exist_ticket  = $ticket;
            $this->brand_id      = $ticket->getBrandId();
            $this->department_id = $ticket->getDepartmentId();
            $this->workflow_id   = $ticket->getWorkflowId();
            $this->product_id    = $ticket->getProductId();
            $this->priority_id   = $ticket->getPriorityId();
            $this->category_id   = $ticket->getCategoryId();
            $this->status        = $ticket->status;

            $field_manager       = App::getSystemService('ticket_fields_manager');
            $this->ticket_fields = $field_manager->createFormArrayForObject($ticket);
            $person              = $person ?: $ticket->person;
            $org                 = $org ?: $person->organization;
        }

        if ($person) {
            $this->custom_person_fields = App::$container->getPersonFieldManager()->createFormArrayForObject($person);
        }

        if ($org) {
            $this->custom_org_fields = App::$container->getOrgFieldManager()->createFormArrayForObject($org);
            $this->organization_id   = $org->getId();
        }
    }

    /**
     * @param callable $callback
     */
    public function setPreSaveCallback($callback)
    {
        $this->_pre_save_callback = $callback;
    }

    /**
     * @throws \Exception
     *
     * @return \Application\DeskPRO\Entity\Ticket
     */
    public function save()
    {
        $this->_em->getConnection()->beginTransaction();
        try {
            $res = $this->_save();
            $this->_em->getConnection()->commit();

            return $res;
        } catch (\Exception $e) {
            $this->_em->getConnection()->rollback();
            throw $e;
        }
    }

    public function setBlobInlineIds(array $ids)
    {
        $this->_blob_inline_ids = $ids;
    }

    protected function _save()
    {
        //------------------------------
        // The user owner
        //------------------------------

        if ($this->person->id) {
            $person = $this->_em->find('DeskPRO:Person', $this->person->id);
        } else {
            $person = App::getSystemService('UsersourceManager')->findPersonByEmail($this->person->email_address);
        }

        if (!$person) {
            if ($this->_person_context->hasPerm('agent_people.create')) {
                $person                = new Person();
                $email_obj             = $person->addEmailAddressString($this->person->email_address);
                $person->primary_email = $email_obj;
            } else {
                throw new \Exception('You do not have permission to create a new user. If you think this is a mistake, please contact your administrator.');
            }
        }

        if ($this->person->organization) {
            $org = $this->_em->getRepository('DeskPRO:Organization')->findOneByName($this->person->organization);
            if (!$org) {
                $org         = new Organization();
                $org['name'] = $this->person->organization;
                $this->_em->persist($org);
            }
            $person->organization = $org;

            if ($this->person->organization_position) {
                $person['organization_position'] = $this->person->organization_position;
            }
        }

        if (!$person->name && $this->person->name) {
            $person->name = $this->person->name;
        }

        if ($this->person->language_id) {
            $person->setLanguageId($this->person->language_id);
        }

        $this->_em->persist($person);
        $this->_em->flush();

        //------------------------------
        // Participants
        //------------------------------

        $add_cc_peopleids = $this->add_cc_person;
        $add_cc_people    = $this->add_cc_newpeople;

        foreach ($this->add_cc_newperson as $info) {
            if (empty($info['email']) || !\Orb\Validator\StringEmail::isValueValid($info['email']) || App::$container->getEmailAccountManager()->findAccountForEmailAddress($info['email'])) {
                continue;
            }

            $check_exist = $this->_em->getRepository('DeskPRO:Person')->findOneByEmail($info['email']);
            if ($check_exist) {
                $add_cc_people[] = $check_exist;
            } else {
                // New person, coming right up
                $new_cc_person = Person::newContactPerson([
                    'email' => $info['email'],
                    'name'  => !empty($info['name']) ? $info['name'] : '',
                ]);
                $this->_em->persist($new_cc_person);

                $add_cc_people[] = $new_cc_person;
            }
        }

        foreach ($add_cc_people as $p) {
            $this->_em->persist($p);
        }

        $add_cc_people = array_merge(
            $add_cc_people,
            $this->_em->getRepository('DeskPRO:Person')->getByIds($add_cc_peopleids)
        );

        $this->_em->flush();

        //------------------------------
        // Ticket
        //------------------------------

        // Ticket props
        $ticket = $this->_ticket_manager->createTicket();

        $ticket_context = $this->_ticket_manager->createAgentExecutorContext($this->_person_context, 'newticket', 'web');

        $ticket['creation_system'] = Ticket::CREATED_WEB_AGENT_PORTAL;
        $ticket['language']        = $person->getRealLanguage();

        if ($this->suppress_user_notify) {
            $ticket_context->getVars()->set('mute_user_emails', true);
        }

        $this->_email = $person->findEmailAddress($this->person->email_address);
        if ($this->_email) {
            $ticket->person_email = $this->_email;
        }

        $standard = [
            'subject', 'status', 'agent_id', 'agent_team_id', 'brand_id',
            'department_id', 'category_id', 'priority_id', 'workflow_id',
            'product_id', 'notify_template',
        ];
        if (!$this->status) {
            $this->status = 'awaiting_agent';
        }

        foreach ($standard as $k) {
            $ticket[$k] = $this->$k;
        }

        $ticket->person = $person;

        //------------------------------
        // Message
        //------------------------------

        // Message
        $message = new TicketMessage();
        $message->setTicket($ticket);
        $message->setPerson($this->_person_context);

        if ($this->is_note) {
            $message->setAsAgentNote(true);
        }

        $message_text = $this->message;
        $formatter    = new SnippetFormatter(App::getContainer()->get('twig'));
        $message_text = $formatter->formatText($message_text, $ticket);

        if ($this->is_html_reply) {
            $message_text = App::get('deskpro.core.input_cleaner')->clean($message_text, 'html');
            $message_text = \Orb\Util\Strings::trimHtml($message_text);
            $message_text = \Orb\Util\Strings::prepareWysiwygHtml($message_text);
            $message->setMessageHtml($message_text);
            $message->setOriginalMessage($this->message);
        } else {
            $message->setMessageText($message_text);
        }

        // Message Attachments
        foreach ($this->attach as $blob_id) {
            $blob = $this->_em->getRepository(Blob::class)->find($blob_id);
            if ($blob) {
                if ($this->_em->getRepository(SnippetTranslation::class)->findSnippetBlob($blob)) {
                    $raw_file = App::$container->get('blob.storage')->copyBlobRecordToString($blob);
                    $blob     = App::$container->get('blob.storage')->createBlobRecordFromString(
                        $raw_file,
                        $blob->getFilename(),
                        $blob->getContentType(),
                        ['tag' => 'ticket_attachment']
                    );
                }

                $blob->setIsTemp(false);

                $attach = new TicketAttachment();
                $attach->setBlob($blob);
                $attach->setPerson($this->_person_context);

                $message->addAttachment($attach);
                $ticket->addAttachment($attach);
            }
        }

        $blobs = App::get('attachment_helper')->processInlineBlobs($message, $this->_blob_inline_ids);
        foreach ($blobs as $blob) {
            $attach            = new TicketAttachment();
            $attach['blob']    = $blob;
            $attach['person']  = $this->_person_context;
            $attach->is_inline = true;
            $message->addAttachment($attach);
            $ticket->addAttachment($attach);
        }

        $message->convertEmbeddedImagesToInlineAttach();

        $ticket->addMessage($message);

        switch ($this->billing_type) {
            case 'amount':
                $ticket->addCharge($this->_person_context, null, floatval($this->billing_amount));
                break;

            case 'time':
                $time = (
                    3600 * $this->billing_hours
                    + 60 * $this->billing_minutes
                    + $this->billing_seconds
                );
                $ticket->addCharge($this->_person_context, $time, null);
        }

        $this->_em->persist($ticket);

        if ($this->_pre_save_callback) {
            call_user_func_array($this->_pre_save_callback, [$ticket, $message, $person]);
        }

        $field_manager      = App::getSystemService('ticket_fields_manager');
        $post_custom_fields = $this->ticket_fields;
        if ($this->ticket_fields) {
            foreach ($this->ticket_fields as $k => $v) {
                $id = Strings::extractRegexMatch('#(\d+)$#', $k);
                if (!$this->layout || $this->layout->hasActiveField('ticket_field_'.$id, $ticket)) {
                    $post_custom_fields[$k] = $v;
                }
            }
        }
        if (!empty($post_custom_fields)) {
            $field_manager->saveFormToObject($post_custom_fields, $ticket);
        }

        $manager                   = App::$container->getPersonFieldManager();
        $post_custom_person_fields = [];
        $custom_person_fields      = $person->isNewPerson() ? $this->post_custom_person_fields : $this->custom_person_fields;
        foreach ($custom_person_fields as $k => $v) {
            $id = Strings::extractRegexMatch('#(\d+)$#', $k);
            if (!$this->layout || $this->layout->hasActiveField('user_field_'.$id, $ticket)) {
                $post_custom_person_fields[$k] = @$post_custom_person_fields[$k] ?: $v;
            } else {
                $post_custom_person_fields[$k] = $v;
            }
        }
        if (!empty($post_custom_person_fields)) {
            $manager->saveFormToObject($post_custom_person_fields, $ticket->person);
        }

        if ($person->organization) {
            $manager                = App::$container->getOrgFieldManager();
            $post_custom_org_fields = [];
            foreach ($this->custom_org_fields as $k => $v) {
                $id = Strings::extractRegexMatch('#(\d+)$#', $k);
                if (!$this->layout || $this->layout->hasActiveField('org_field_'.$id, $ticket)) {
                    $post_custom_org_fields[$k] = @$this->post_custom_org_fields[$k] ?: $v;
                } else {
                    $post_custom_org_fields[$k] = $v;
                }
            }
            if (!empty($post_custom_org_fields)) {
                $manager->saveFormToObject($post_custom_org_fields, $ticket->person->organization);
            }
        }

        foreach ($add_cc_people as $add_cc_person) {
            if ($add_cc_person->getId() != $ticket->person->getId()) {
                $part = $ticket->addParticipantPerson($add_cc_person);
                if ($part) {
                    $this->_em->persist($part);
                }
            }
        }

        if ($this->add_followers) {
            $ticket->setParticipantAgentIds($this->add_followers);
        }

        $this->_em->persist($ticket);
        $this->_em->persist($message);

        //------------------------------
        // per-person and per-org fields
        //------------------------------
        $new_field_manager = App::$container->getCustomFieldManager();
        $new_custom_fields = $new_field_manager->createFormForOwner($ticket, $ticket->person, $this->layout, ['allow_edit' => true]);
        if ($org = $ticket->person->organization) {
            $new_field_manager->merge($new_custom_fields, $new_field_manager->createFormForOwner(
                $ticket, $org, $this->layout, ['allow_edit' => true]
            ));
        }
        $new_custom_fields->handleRequest(App::$container->getRequest());

        $this->_ticket_manager->saveTicket($ticket, $ticket_context);
        $this->_ticket = $ticket;

        return $this->_ticket;
    }

    /**
     * @return \Application\DeskPRO\Entity\Ticket
     */
    public function getTicket()
    {
        return $this->_ticket;
    }
}
