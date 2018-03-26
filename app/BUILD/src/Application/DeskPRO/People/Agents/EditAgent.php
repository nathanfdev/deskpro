<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\People\Agents;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PhoneNumber;
use Application\DeskPRO\ORM\CollectionHelper;
use Application\DeskPRO\Validator\Constraints as DeskproConstraints;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;
use Orb\Util\PhoneNumbers;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

class EditAgent
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    private $agent;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $override_name;

    /**
     * @var string[]
     */
    public $zones;

    /**
     * @var string[]
     */
    public $emails;

    /**
     * @var \Application\DeskPRO\Entity\AgentTeam[]
     */
    public $teams;

    /**
     * @var \Application\DeskPRO\Entity\Usergroup[]
     */
    public $agent_groups;

    /**
     * @var \Application\DeskPRO\Entity\AgentTeam
     */
    public $primary_team;

    /**
     * @var array
     */
    public $notification_settings;

    /**
     * @param Person $person
     */
    public function __construct(Person $person)
    {
        $this->agent         = $person;
        $this->name          = $person->name;
        $this->override_name = $person->override_display_name;
        $this->primary_phone = $person->getPrimaryPhoneNumber() ?: new PhoneNumber();

        $this->zones = [];
        if ($person->can_admin) {
            $this->zones[] = 'admin';
        }
        if ($person->can_reports) {
            $this->zones[] = 'reports';
        }

        $this->emails = [];
        if ($person->primary_email) {
            $this->emails[] = $person->primary_email->email;
        }
        foreach ($person->emails as $email) {
            $this->emails[] = $email;
        }
        $this->emails = array_unique($this->emails);

        $this->teams = [];

        if ($person->id) {
            $person->loadHelper('AgentTeam');
            $this->teams = $person->getHelper('AgentTeam')->getAgentTeams();
        }

        $this->agent_groups = $person->usergroups->toArray();

        $this->notification_settings = [
            'no_allow_set_email'   => (int) $person->getPref('agent_notif.no_allow_set_email'),
            'no_allow_set_browser' => (int) $person->getPref('agent_notif.no_allow_set_browser'),
        ];

        $this->primary_team = $person->primary_team;
    }

    /**
     * Saves the agent.
     *
     * @param EntityManager $em
     *
     * @return Person
     */
    public function save(EntityManager $em)
    {
        $agent = $this->agent;

        //------------------------------
        // General props
        //------------------------------

        $agent->is_user      = true;
        $agent->is_confirmed = true;
        $agent->is_agent     = true;
        $agent->can_agent    = true;

        $em->persist($agent);

        $agent->name                  = $this->name;
        $agent->override_display_name = $this->override_name ?: '';

        if (!PhoneNumbers::looksEmpty($this->primary_phone['number'])) {
            $agent->setPrimaryPhoneNumber($this->primary_phone);
        } else {
            $agent->setPrimaryPhoneNumber(null);
        }

        $agent->can_admin   = in_array('admin', $this->zones);
        $agent->can_reports = in_array('reports', $this->zones);

        //------------------------------
        // Teams
        //------------------------------

        foreach ($agent->teams as $team) {
            /* @var $team AgentTeam */
            $team->removePerson($agent); // unidirectional
        }

        $found_primary = false;

        foreach ($this->teams as $team) {
            /* @var $team AgentTeam */
            $agent->addTeam($team); // bidirectional

            if ($team === $this->primary_team) {
                $found_primary = true;
            }
        }

        if (!$found_primary) {
            if ($this->teams) {
                $this->primary_team = Arrays::getFirstItem($this->teams);
            } else {
                $this->primary_team = null;
            }
        }

        //------------------------------
        // Groups
        //------------------------------

        $group_coll_helper = new CollectionHelper($agent, 'usergroups', null, function ($item) {
            return !$item->is_agent_group;
        });
        if ($this->agent_groups instanceof ArrayCollection) {
            $this->agent_groups = $this->agent_groups->toArray();
        }
        $group_coll_helper->setCollection($this->agent_groups);

        //------------------------------
        // Email addresses
        //------------------------------

        $set_emails = array_map(function ($x) {
            return strtolower($x);
        },        $this->emails);
        $have_emails = array_map(function ($y) {
            return strtolower($y->email);
        }, $agent->emails->toArray());

        $add_emails = array_diff($set_emails, $have_emails);
        $del_emails = array_diff($have_emails, $set_emails);

        foreach ($add_emails as $email_address) {
            $email               = new PersonEmail();
            $email->person       = $agent;
            $email->email        = $email_address;
            $email->is_validated = true;

            $agent->addEmailAddress($email);
            $em->persist($email);
        }

        foreach ($del_emails as $email_address) {
            $email = $agent->findEmailAddress($email_address);
            if ($email) {
                $agent->removeEmailAddressId($email['id']);
                $em->remove($email);
            }
        }

        $primary_email_address = strtolower(Arrays::getFirstItem($this->emails));
        foreach ($agent->emails as $email) {
            if (strtolower($email->email) == $primary_email_address) {
                $agent->primary_email = $email;
                break;
            }
        }

        if (!$agent->primary_email) {
            foreach ($agent->emails as $email) {
                $agent->primary_email = $email;
                break;
            }
        }

        $em->flush();

        foreach ($this->notification_settings as $k => $v) {
            $p = $agent->setPreference('agent_notif.'.$k, (int) $v);
            $em->persist($p);
        }

        $em->persist($agent);
        $agent->primary_team = $this->primary_team;

        $em->flush();
    }

    //###########################################################################
    // Validation Metadata
    //###########################################################################

    public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new Constraints\NotBlank([
            'message' => 'Name should not be blank.',
        ]));
        $metadata->addPropertyConstraint('emails', new Constraints\All([
            'constraints' => [
                new Constraints\NotBlank(),
                new Constraints\Email(),
            ],
        ]));

        $metadata->addPropertyConstraint('emails', new Constraints\Count(['min' => 1, 'minMessage' => '[emails_count] At least one email address is required']));

        $metadata->addPropertyConstraint('teams', new Constraints\All([
            'constraints' => [
                new DeskproConstraints\AgentTeamConstraint(),
            ],
        ]));
        $metadata->addPropertyConstraint('agent_groups', new Constraints\All([
            'constraints' => [
                new DeskproConstraints\AgentGroupConstraint(),
            ],
        ]));
    }
}
