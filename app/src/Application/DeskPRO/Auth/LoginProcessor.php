<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage DeskPRO
 */

namespace Application\DeskPRO\Auth;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonContactData;
use Application\DeskPRO\Entity\PersonUsersourceAssoc;
use Application\DeskPRO\Entity\Usersource;
use Doctrine\ORM\EntityManager;
use Orb\Auth\Identity;
use Orb\Util\Arrays;
use Orb\Util\OptionsArray;

class LoginProcessor
{
    /**
     * The users identity
     * @var \Orb\Auth\Identity
     */
    protected $identity;

    /**
     * The usersource
     * @var \Application\DeskPRO\Entity\Usersource
     */
    protected $usersource;

    /**
     * The association
     * @var \Application\DeskPRO\Entity\PersonUsersourceAssoc
     */
    protected $assoc;

    /**
     * The person the login represents
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var bool wether we used "new Person()" to create a new user or not
     */
    protected $new_person;
    /**
     * @var bool
     */
    private $test_mode;


    /**
     * @param Usersource $usersource
     * @param Identity   $identity
     * @param bool       $testMode   true if we shouldn't do anything permanent (persist to db, mail)
     */
    public function __construct(Usersource $usersource, Identity $identity, $testMode = false)
    {
        $this->identity = $identity;
        $this->usersource = $usersource;
        $this->new_person = false;
        $this->test_mode = $testMode;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        if ($this->person !== null) return $this->person;

        #------------------------------
        # Figure if we have an existing Person mapped, or if its
        # a new Person
        #------------------------------

        $em = App::getOrm();
        /** @var \Application\DeskPRO\EntityRepository\PersonUsersourceAssoc $assoc_repos */
        $assoc_repos = $em->getRepository('DeskPRO:PersonUsersourceAssoc');

        $this->beginTransaction($em);

        $this->assoc = $assoc_repos->getIdentityAssociation(
            $this->usersource,
            $this->identity->getIdentity()
        );

        $mapped_fields = $this->usersource->getAdapter()->getFieldsFromIdentity($this->identity);
        $mapped_fields = Arrays::removeEmptyString($mapped_fields);
        $mapped_fields = new OptionsArray($mapped_fields);

        #------------------------------
        # If we dont have one yet, we're have to create the assoc and maybe a new user too
        #------------------------------

        if (!$this->assoc) {

            $this->person = null;

            // If we can trust the email address and there already exists a person
            // with this email address, then we can just link the accounts now
            $set_email = false;
            if ($mapped_fields->has('email') && $mapped_fields->get('email_confirmed')) {
                $set_email = $mapped_fields->get('email');
                $email = App::getEntityRepository('DeskPRO:PersonEmail')->getEmail($mapped_fields->get('email'));
                if ($email) {
                    $this->person = $email->person;
                }
            }

            // if someone has already associated this twitter account with them, then connect with them
            if ($mapped_fields->has('twitter')) {
                $twitter = $mapped_fields->get('twitter');
                $this->person = App::getEntityRepository('DeskPRO:PersonTwitterUser')->getVerifiedPersonForTwitterUser($twitter['user_id']);
            }

            if (!$this->person) {
                $this->new_person = true;
                $this->person = new Person();
                $this->person->is_user = true;
                $this->person->creation_system = 'web.usersource';
            }

            $this->updatePersonName($mapped_fields);

            if ($mapped_fields->has('picture_data') && !$this->person->picture_blob) {
                $filename = tempnam(dp_get_tmp_dir(), 'picture');
                $fp = @fopen($filename, 'w');
                if ($fp) {
                    @fwrite($fp, $mapped_fields->get('picture_data'));
                    @fclose($fp);

                    $mime_map = array(
                        IMAGETYPE_GIF => array('gif', 'image/gif'),
                        IMAGETYPE_JPEG => array('jpg', 'image/jpeg'),
                        IMAGETYPE_PNG => array('png', 'image/png')
                    );
                    $image_info = getimagesize($filename);
                    if ($image_info && $image_info[0] && $image_info[1] && isset($mime_map[$image_info[2]])) {
                        $mime = $mime_map[$image_info[2]];
                        $file = new \Symfony\Component\HttpFoundation\File\UploadedFile(
                            $filename, 'picture.' . $mime[0], $mime[1], strlen($mapped_fields->get('picture_data'))
                        );

                        $accept = App::getContainer()->getAttachmentAccepter();
                        $blob = $accept->accept($file);
                        $this->person->setPictureBlob($blob);
                    }
                }
                @unlink($filename);
            }

            $this->persist($em, $this->person);

            // TODO: we need to update this to the person phone_number field when we deprecate the contact data phone number
            if ($mapped_fields->has('phone')) {
                $contact_data = new PersonContactData();
                $contact_data->contact_type = 'phone';
                $contact_data->applyFormData(array(
                    'number' => $mapped_fields->get('phone')
                ));

                $contact_data->person = $this->person;

                $this->persist($em, $contact_data);
            }

            $this->flush($em);

            if ($set_email && !$this->person->findEmailAddress($set_email)) {
                $email_obj = $this->person->addEmailAddressString($set_email);
                $this->persist($em, $email_obj);
                $this->flush($em);
            }

            if ($mapped_fields->has('twitter')) {
                $twitter = $mapped_fields->get('twitter');

                App::getDb()->executeUpdate("
                    INSERT INTO people_twitter_users
                        (person_id, twitter_user_id, screen_name, is_verified, oauth_token, oauth_token_secret)
                    VALUES (?, ?, ?, 1, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        twitter_user_id = VALUES(twitter_user_id),
                        screen_name = VALUES(screen_name),
                        is_verified = 1,
                        oauth_token = VALUES(oauth_token),
                        oauth_token_secret = VALUES(oauth_token_secret)
                ", array($this->person->id, $twitter['user_id'], $twitter['screen_name'], $twitter['oauth_token'], $twitter['oauth_token_secret']));

                $has_account = false;
                foreach ($this->person->getContactData('twitter') AS $twitter_details) {
                    if ($twitter_details->field_1 == $twitter['screen_name'] || ($twitter_details->field_3 && $twitter_details->field_3 == $twitter['user_id'])) {
                        $twitter_details->field_10 = '1';
                        $this->persist($em, $twitter_details);
                        $has_account = true;
                    }
                }

                if (!$has_account) {
                    $twitter_details = new \Application\DeskPRO\Entity\PersonContactData();
                    $twitter_details->contact_type = 'twitter';
                    $twitter_details->person = $this->person;
                    $twitter_details->field_1 = $twitter['screen_name'];
                    $twitter_details->field_2 = '0';
                    $twitter_details->field_3 = $twitter['user_id'];
                    $twitter_details->field_10 = '1';
                    $this->persist($em, $twitter_details);
                }

                $this->flush($em);
            }

            // New assoc
            $this->assoc = new PersonUsersourceAssoc();
            $this->assoc['person']            = $this->person;
            $this->assoc['usersource']        = $this->usersource;
            $this->assoc['identity']          = $this->identity->getIdentity();
            $this->assoc['identity_friendly'] = $this->identity->getFriendlyIdentity() ?: $this->identity->getIdentity();
            $this->assoc['data']              = $this->identity->getRawData();
            $this->persist($em, $this->assoc);
            $this->flush($em);

        #------------------------------
        # The assoc exists
        #------------------------------

        } else {
            $this->person = $this->assoc['person'];

            if (App::getSetting('core.usersource_always_update_data')) {
                $this->updatePersonName($mapped_fields);
            }

            // Need to make sure the email address on the local account matches that of the
            // identity (it could have been updated).
            if ($mapped_fields->has('email') && $mapped_fields->get('email_confirmed')) {
                if (!$this->person->hasEmailAddress($mapped_fields->get('email'))) {
                    $email = App::getEntityRepository('DeskPRO:PersonEmail')->getEmail($mapped_fields->get('email'));
                    if (!$email) {
                        $email_obj = $this->person->addEmailAddressString($mapped_fields->get('email'));
                        $this->persist($em, $email_obj);
                        $this->person->primary_email = $email_obj;
                        $this->persist($em, $this->person);
                        $this->flush($em);
                    }
                }
            }
        }

        // Update custom field data
        App::getSystemService('person_fields_manager')->copyUsersourceData(
            $this->person,
            $this->identity,
            $this->usersource
        );

        $this->person['is_user'] = true;
        $this->person->setLastLoginAt();

        if (Usersource::TYPE_AGENT == $this->usersource->type && $this->usersource->auto_agent) {
            $agentChecker = App::getSystemService('agent_checker');
            if ($agentChecker->addAgentSeat($this->person)) {
                $this->person['is_agent']  = true;
                $this->person['can_agent'] = true;
                if ($this->usersource->agent_permission_group) {
                    $this->person->addUsergroup($this->usersource->agent_permission_group);
                }
            }
            // wont send mail in test mode
            $this->sendAgentWelcomeEmail();
        }

        $this->persist($em, $this->person);
        $this->persist($em, $this->assoc);
        $this->flush($em);
        $this->commit($em);

        return $this->person;
    }


    public function beginTransaction(EntityManager $em)
    {
        if (!$this->test_mode) $em->beginTransaction();
    }

    public function commit(EntityManager $em)
    {
        if (!$this->test_mode) $em->commit();
    }


    public function persist(EntityManager $em, $entity)
    {
        if (!$this->test_mode) $em->persist($entity);
    }

    public function flush(EntityManager $em)
    {
        if (!$this->test_mode) $em->flush();
    }


    protected function sendAgentWelcomeEmail()
    {
        if (!$this->test_mode) {
            if ($this->new_person && $this->person->getPrimaryEmail() && $this->person->is_agent) {
                $message = App::$container->getMailer()->createMessage();
                $message->setToPerson($this->person);
                $message->setTemplate(
                    'DeskPRO:emails_agent:agent-welcome-usersource.html.twig',
                    array(
                        'agent'      => $this->person,
                        'usersource' => $this->usersource
                    )
                );
                $attach = \Swift_Attachment::fromPath(
                    DP_ROOT . '/src/Application/AgentBundle/Resources/assets/agent-quickstart/en_US.pdf',
                    'application/pdf'
                );
                $attach->setFilename('Getting Started with DeskPRO.pdf');
                $message->attach($attach);
                App::$container->getMailer()->send($message);
            }
        }
    }

    /**
     * @param $mapped_fields
     */
    protected function updatePersonName($mapped_fields)
    {
        foreach (array('first_name', 'last_name', 'name') as $k) {
            if ($mapped_fields->has($k)) {
                $this->person[$k] = $mapped_fields->get($k);
            }
        }
    }
}
