<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\PasswordHistory;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonPhoneNumber;
use Orb\Util\PhoneNumbers;

class SettingsProfile
{
    /** @var string */
    public $name;
    /** @var string */
    public $override_display_name;
    /** @var string */
    public $email;
    /** @var string */
    public $timezone = 'UTC';
    /** @var int|null */
    public $language_id = 0;
    /** @var string */
    public $password = '';
    /** @var string */
    public $password2 = '';
    /** @var bool */
    public $new_picture_blob_id = false;
    /** @var bool */
    public $remove_picture = false;

    /** @var bool */
    public $ticket_close_reply = false;
    /** @var bool */
    public $ticket_close_note = false;
    /** @var bool */
    public $hide_claimed_chat = false;
    /** @var bool */
    public $ticket_go_next_reply = false;
    /** @var bool */
    public $ticket_reverse_order = false;
    /** @var bool */
    public $enable_plaintext_email = false;
    /** @var int */
    public $default_team_id = 0;
    /** @var bool */
    public $reset_api_token = false;

    /** @var int|mixed */
    public $auto_dismiss_notifications = 60;

    /** @var array */
    public $new_emails;
    /** @var array */
    public $remove_emails;

    public $primary_phone;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(Person $person, $defaultCountryCode = 'US')
    {
        $this->em = App::getOrm();

        $this->person = $person;

        $this->name = $person->name;

        // store the text, for the user to operate on, but keep track of the PhoneNumber object (or create a new one)
        // this is acting like a DataTransformer.
        $this->primary_phone = $person->getPrimaryPhoneNumber() ?: new PersonPhoneNumber();
        if (!$this->primary_phone['region']) {
            $this->primary_phone['region'] = $defaultCountryCode;
        }

        $this->override_display_name = $person->override_display_name;
        $this->email                 = $person->getPrimaryEmailAddress();
        $this->timezone              = $person->timezone;
        $this->language_id           = $person->getLanguage()->getId();

        $this->ticket_close_reply     = (bool) $person->getPref('agent.ticket_close_reply', true);
        $this->ticket_close_note      = (bool) $person->getPref('agent.ticket_close_note', false);
        $this->ticket_go_next_reply   = (bool) $person->getPref('agent.ticket_go_next_reply', false);
        $this->hide_claimed_chat      = (bool) $person->getPref('agent.hide_claimed_chat', false);
        $this->default_team_id        = $person->getPref('agent.ticket_default_team_id');
        $this->ticket_reverse_order   = (bool) $person->getPref('agent.ticket_reverse_order');
        $this->enable_plaintext_email = (bool) $person->getPref('agent.enable_plaintext_email');
        if ($this->default_team_id === null) {
            $teams                 = $person->getAgent()->getTeams();
            $last_team             = end($teams);
            $this->default_team_id = $last_team ? $last_team->id : 0;
        }
        $this->auto_dismiss_notifications = $person->getPref('agent.ui.auto_dismiss_notification', 60);
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function requiresAuth()
    {
        if ($this->password || $this->email != $this->person->getPrimaryEmailAddress()) {
            return true;
        }

        return false;
    }

    public function save()
    {
        $person = $this->person;

        $person->name = $this->name;

        if (PhoneNumbers::looksEmpty($this->primary_phone['number'])) {
            $person->setPrimaryPhoneNumber(null);
        } else {
            // just update the $primary->number text of the existing primary PhoneNumber object
            $person->setPrimaryPhoneNumber($this->primary_phone);
        }

        $person->override_display_name = $this->override_display_name;
        $person->timezone              = $this->timezone;

        if ($this->new_picture_blob_id) {
            $blob = $this->em->getRepository('DeskPRO:Blob')->getByAuthId($this->new_picture_blob_id);
            if ($blob) {
                $person->picture_blob = $blob;
            }
        }

        if ($this->remove_picture && $person->hasPicture()) {
            if ($person->getPictureBlob()) {
                App::$container->getBlobStorage()->deleteBlobRecord($person->getPictureBlob());
            }
            $person->setPictureBlob(null);
        }

        $primary_email = $person->getPrimaryEmail();
        if ($primary_email->email != $this->email) {
            $found_email = $person->findEmailAddress($this->email);
            if ($found_email) {
                $new_primary_email = $found_email;
            } else {
                $new_primary_email               = new \Application\DeskPRO\Entity\PersonEmail();
                $new_primary_email->email        = $this->email;
                $new_primary_email->is_validated = true;
                $person->addEmailAddress($new_primary_email);
                $this->em->persist($new_primary_email);
            }

            $person->primary_email = $new_primary_email;

            $person->removeEmailAddressId($primary_email->id);
            $this->em->remove($primary_email);
        }

        if ($this->password) {
            $person->setPassword($this->password);

            if ($this->person->password && $this->person->password_scheme == 'bcrypt') {
                $history                  = new PasswordHistory();
                $history->person          = $this->person;
                $history->password_scheme = $this->person->password_scheme;
                $history->password        = $this->person->password;
                $this->em->persist($history);
            }

            // Delete old sessions for this user
            $this->em->getConnection()->delete('sessions', ['person_id' => $this->person->getId()]);
        }

        if ($this->language_id) {
            $person->setLanguageId($this->language_id);
        }

        $person->setPreference('agent.ticket_close_reply', $this->ticket_close_reply ? 1 : 0);
        $person->setPreference('agent.ticket_close_note', $this->ticket_close_note ? 1 : 0);
        $person->setPreference('agent.ticket_go_next_reply', $this->ticket_go_next_reply ? 1 : 0);
        $person->setPreference('agent.hide_claimed_chat', $this->hide_claimed_chat ? 1 : 0);
        $person->setPreference('agent.ticket_reverse_order', $this->ticket_reverse_order ? 1 : 0);
        $person->setPreference('agent.enable_plaintext_email', $this->enable_plaintext_email ? 1 : 0);

        $assign_team_setting = (
            App::getSetting('core_tickets.new_assignteam') == 'assign'
            || App::getSetting('core_tickets.reply_assignteam_assigned') == 'assign'
            || App::getSetting('core_tickets.reply_assignteam_unassigned') == 'assign'
        );
        $primaryTeam = $person->getPrimaryTeam();
        if ($primaryTeam && $assign_team_setting) {
            $person->setPreference('agent.ticket_default_team_id', $this->default_team_id ? (int) $this->default_team_id : (int) $primaryTeam['id']);
        }

        $person->setPreference('agent.ui.auto_dismiss_notification', intval($this->auto_dismiss_notifications));

        if ($this->reset_api_token) {
            $token = App::getEntityRepository('DeskPRO:ApiToken')->getTokenForPerson($person);
            if ($token) {
                $token->regenerateToken();
                $this->em->persist($token);
            }
        }

        $this->em->persist($person);

        $this->em->beginTransaction();

        try {
            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        // Additional email addresses
        foreach ($this->new_emails as $new_email) {
            if ($person->hasEmailAddress($new_email)) {
                continue;
            }

            $email_address = $person->addEmailAddressString($new_email);
            $this->em->persist($email_address);
            $this->em->flush();
        }

        // Removing email addresses
        foreach ($this->remove_emails as $remove_email_id) {
            $person->removeEmailAddressId($remove_email_id);
            $this->em->flush();
        }
    }
}
