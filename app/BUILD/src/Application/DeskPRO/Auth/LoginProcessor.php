<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Auth;

use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\SystemServices\AgentCheckerService;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonTwitterUser;
use Application\DeskPRO\Entity\PersonUsersourceAssoc;
use Application\DeskPRO\Entity\PhoneNumber;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\Actions\AbstractAction;
use DeskPRO\Bundle\AppBundle\Exception\UsersourceNoEmailException;
use Doctrine\ORM\EntityManager;
use Orb\Auth\Identity;
use Orb\Util\Arrays;
use Orb\Util\OptionsArray;

class LoginProcessor
{
    /**
     * The users identity.
     *
     * @var \Orb\Auth\Identity
     */
    protected $identity;

    /**
     * The usersource.
     *
     * @var \Application\DeskPRO\Entity\Usersource
     */
    protected $usersource;

    /**
     * The association.
     *
     * @var \Application\DeskPRO\Entity\PersonUsersourceAssoc
     */
    protected $assoc;

    /**
     * The person the login represents.
     *
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
        $this->identity   = $identity;
        $this->usersource = $usersource;
        $this->new_person = false;
        $this->test_mode  = $testMode;
    }

    /**
     * @param string $use_email_address if there is no email found in the usersource data, then use this one
     *
     * @return Person
     */
    public function getPerson($use_email_address = null)
    {
        if ($this->person !== null) {
            return $this->person;
        }

        //------------------------------
        // Figure if we have an existing Person mapped, or if its
        // a new Person
        //------------------------------

        $em = App::getOrm();
        /** @var \Application\DeskPRO\EntityRepository\PersonUsersourceAssoc $assocRepos */
        $assocRepos = $em->getRepository(PersonUsersourceAssoc::class);

        $this->beginTransaction($em);

        $this->assoc = $assocRepos->getIdentityAssociation(
            $this->usersource,
            $this->identity->getIdentity()
        );

        $mappedFields = $this->usersource->getAdapter()->getFieldsFromIdentity($this->identity);
        $mappedFields = Arrays::removeEmptyString($mappedFields);
        $mappedFields = new OptionsArray($mappedFields);

        //------------------------------
        // If we dont have one yet, we're have to create the assoc and maybe a new user too
        //------------------------------

        if (!$this->assoc) {
            $this->person = null;

            // If we can trust the email address and there already exists a person
            // with this email address, then we can just link the accounts now
            $setEmail = false;
            // I removed the "email_confirmed" requirement below; after new validation rules, all emails from a usersource are considered valid
            if ($mappedFields->has('email') || $use_email_address) {
                $setEmail = $mappedFields->get('email', $use_email_address);
                $email    = App::getEntityRepository(PersonEmail::class)->getEmail($mappedFields->get('email'));
                /** @var PersonEmail $email */
                if ($email) {
                    // always validate emails sent from a usersource
                    $email->is_validated = true;
                    $this->person        = $email->person;
                }
            }

            // if someone has already associated this twitter account with them, then connect with them
            if ($mappedFields->has('twitter')) {
                $twitter      = $mappedFields->get('twitter');
                $this->person = App::getEntityRepository(PersonTwitterUser::class)->getVerifiedPersonForTwitterUser($twitter['user_id']);
            }

            if (!$this->person) {
                if (!$setEmail) {
                    $em->rollback();
                    // we are making a new person, and no email was sent in. this is not possible. throw an exception:
                    throw new UsersourceNoEmailException('The account you are trying to use is invalid because it is missing an email address.');
                }
                $this->new_person = true;
                $this->person     = new Person();
                 // always validate people sent from a usersource
                $this->person->is_user         = true;
                $this->person->creation_system = 'web.usersource';
            }

            $this->updatePersonName($mappedFields);
            $this->updatePictureData($mappedFields, $em);
            $this->updatePhone($mappedFields, $em);
            $this->updateTwitter($mappedFields, $em);

            if ($setEmail && !$this->person->findEmailAddress($setEmail)) {
                $emailObj = $this->person->addEmailAddressString($setEmail);
                // always validate emails sent from a usersource
                if ($thisEmail = $this->person->findEmailAddress($setEmail)) {
                    $thisEmail->is_validated = true;
                }
                $this->persist($em, $emailObj);
                $this->flush();
            }

            // New assoc
            $this->assoc                      = new PersonUsersourceAssoc();
            $this->assoc['person']            = $this->person;
            $this->assoc['usersource']        = $this->usersource;
            $this->assoc['identity']          = $this->identity->getIdentity();
            $this->assoc['identity_friendly'] = $this->identity->getFriendlyIdentity() ?: $this->identity->getIdentity();
            $this->assoc['data']              = $this->identity->getRawData();
            $this->persist($em, $this->assoc);
            $this->flush();

        //------------------------------
        // The assoc exists
        //------------------------------
        } else {
            $this->person = $this->assoc['person'];

            // if this setting is true, then always update $this->person via these methods
            if (App::getSetting('core.usersource_always_update_data')) {
                $this->updatePersonName($mappedFields);
                if (!$this->person->picture_blob || strpos($this->person->picture_blob->filename, 'dp-source-picture') !== false) {
                    $this->updatePictureData($mappedFields, $em);
                }
                $this->updatePhone($mappedFields, $em);
                $this->updateTwitter($mappedFields, $em);
            }

            // Need to make sure the email address on the local account matches that of the
            // identity (it could have been updated).
            if ($mappedFields->has('email') && $mappedFields->get('email_confirmed')) {
                if (!$this->person->hasEmailAddress($mappedFields->get('email'))) {
                    $email = App::getEntityRepository('DeskPRO:PersonEmail')->getEmail($mappedFields->get('email'));
                    if (!$email) {
                        $emailObj = $this->person->addEmailAddressString($mappedFields->get('email'));
                        $this->persist($em, $emailObj);
                        // always validate emails sent from a usersource
                        $emailObj->is_validated      = true;
                        $this->person->primary_email = $emailObj;
                        $this->persist($em, $this->person);
                        $this->flush();
                    }
                }
            }
        }

        // Update custom field data
        App::$container->getPersonFieldManager()->copyUsersourceData(
            $this->person,
            $this->identity,
            $this->usersource
        );

        $this->person['is_user'] = true;
        $this->person->setLastLoginAt();

        self::tryUsergroupPromotion($this->usersource, $this->person, $this->identity->getRawData());
        if (self::tryAutoAgent($this->usersource, $this->person)) {
            $this->sendAgentWelcomeEmail();
        }

        // any user who logs in via a usersource is automatically considered to be a user and confirmed
        $this->person->is_confirmed = true;
        $this->person->is_user      = true;

        $this->persist($em, $this->person);
        $this->persist($em, $this->assoc);
        $this->flush();
        $this->commit($em);

        return $this->person;
    }

    public function beginTransaction(EntityManager $em)
    {
        if (!$this->test_mode) {
            $em->beginTransaction();
        }
    }

    public function commit(EntityManager $em)
    {
        if (!$this->test_mode) {
            $em->commit();
        }
    }

    public function persist(EntityManager $em, $entity)
    {
        if (!$this->test_mode) {
            $em->persist($entity);
        }
    }

    public function flush(EntityManager $em)
    {
        if (!$this->test_mode) {
            $em->flush();
        }
    }

    protected function sendAgentWelcomeEmail()
    {
        if (!$this->test_mode) {
            if ($this->new_person && $this->person->getPrimaryEmail() && $this->person->is_agent) {
                $attach = \Swift_Attachment::fromPath(
                    DP_ROOT.'/src/Application/AgentBundle/Resources/assets/agent-quickstart/en_US.pdf',
                    'application/pdf'
                );
                $attach->setFilename('Getting Started with DeskPRO.pdf');
                if (App::$container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                    $agentPassword = $this->person->getPlaintextPassword();
                    $viewModel     = App::$container->get('email.agent_viewmodel_factory')
                        ->createAgentWelcomeUsersourceModel($agentPassword);
                    App::$container->get('email.email_sender')
                        ->send($viewModel,
                            [
                                'to'          => $this->person,
                                'attachments' => [$attach],
                            ]
                        );
                } else {
                    $message = App::$container->getMailer()->createMessage();
                    $message->setToPerson($this->person);
                    $message->setTemplate(
                        'DeskPRO:emails_agent:agent-welcome-usersource.html.twig',
                        [
                            'agent'      => $this->person,
                            'usersource' => $this->usersource,
                        ]
                    );
                    $message->attach($attach);
                    App::$container->getMailer()->send($message);
                }
            }
        }
    }

    /**
     * @param $mapped_fields
     */
    protected function updatePersonName($mapped_fields)
    {
        foreach (['first_name', 'last_name', 'name'] as $k) {
            if ($mapped_fields->has($k)) {
                $this->person[$k] = $mapped_fields->get($k);
            }
        }
    }

    /**
     * @param $mapped_fields
     * @param $em
     *
     * @throws \Exception
     */
    protected function updateTwitter($mapped_fields, $em)
    {
        if ($mapped_fields->has('twitter')) {
            $twitter = $mapped_fields->get('twitter');

            // its possible that this is a new user that is not yet persisted. do this so query below runs ok.
            if (!$this->person->id) {
                $em = App::getOrm();
                $em->persist($this->person);
                $em->flush();
            }

            App::getDb()->executeUpdate('
                    INSERT INTO people_twitter_users
                        (person_id, twitter_user_id, screen_name, is_verified, oauth_token, oauth_token_secret)
                    VALUES (?, ?, ?, 1, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        twitter_user_id = VALUES(twitter_user_id),
                        screen_name = VALUES(screen_name),
                        is_verified = 1,
                        oauth_token = VALUES(oauth_token),
                        oauth_token_secret = VALUES(oauth_token_secret)
                ', [$this->person->id, $twitter['user_id'], $twitter['screen_name'], $twitter['oauth_token'], $twitter['oauth_token_secret']]);

            $has_account = false;
            foreach ($this->person->getContactData('twitter') as $twitter_details) {
                if ($twitter_details->field_1 == $twitter['screen_name'] || ($twitter_details->field_3 && $twitter_details->field_3 == $twitter['user_id'])) {
                    $twitter_details->field_10 = '1';
                    $this->persist($em, $twitter_details);
                    $has_account = true;
                }
            }

            if (!$has_account) {
                $twitter_details               = new \Application\DeskPRO\Entity\PersonContactData();
                $twitter_details->contact_type = 'twitter';
                $twitter_details->person       = $this->person;
                $twitter_details->field_1      = $twitter['screen_name'];
                $twitter_details->field_2      = '0';
                $twitter_details->field_3      = $twitter['user_id'];
                $twitter_details->field_10     = '1';
                $this->persist($em, $twitter_details);
            }

            $this->flush();
        }
    }

    /**
     * @param $mapped_fields
     * @param $em
     */
    protected function updatePictureData($mapped_fields, $em)
    {
        if ($mapped_fields->has('picture_data')) {
            $filename = tempnam(dp_get_tmp_dir(), 'picture');
            $fp       = @fopen($filename, 'w');
            if ($fp) {
                @fwrite($fp, $mapped_fields->get('picture_data'));
                @fclose($fp);

                $mime_map = [
                    IMAGETYPE_GIF  => ['gif', 'image/gif'],
                    IMAGETYPE_JPEG => ['jpg', 'image/jpeg'],
                    IMAGETYPE_PNG  => ['png', 'image/png'],
                ];
                $image_info = getimagesize($filename);
                if ($image_info && $image_info[0] && $image_info[1] && isset($mime_map[$image_info[2]])) {
                    $mime = $mime_map[$image_info[2]];
                    $file = new \Symfony\Component\HttpFoundation\File\UploadedFile(
                        $filename, 'dp-source-picture.'.$mime[0], $mime[1], strlen($mapped_fields->get('picture_data'))
                    );

                    $accept = App::getContainer()->getAttachmentAccepter();
                    $blob   = $accept->accept($file);
                    if ($this->person->getPictureBlob()) {
                        App::getContainer()->getBlobStorage()->deleteBlobRecord(
                            $this->person->getPictureBlob()
                        );
                    }
                    $this->person->setPictureBlob($blob);
                }
            }
            @unlink($filename);
        }

        $this->persist($em, $this->person);
    }

    /**
     * @param $mapped_fields
     * @param $em
     */
    private function updatePhone($mapped_fields, $em)
    {
        if ($mapped_fields->has('phone')) {
            if ($number = PhoneNumber::createEntity($mapped_fields->get('phone'))) {
                $this->person->setPrimaryPhoneNumber($number);

                $this->persist($em, $this->person);
            }
        }
    }

    /**
     * Returns true if this method made the person an agent, false otherwise.
     *
     * NOTE: true does not mean they were not an agent before, it just means it passed all the checks to
     *       qualify to be a person that can go through the "auto-agent" process, and that we made sure
     *       they are now an agent.
     *
     * @param Usersource $usersource
     * @param Person     $person
     *
     * @return bool
     */
    public static function tryAutoAgent(Usersource $usersource, Person $person)
    {
        if (Usersource::TYPE_AGENT === $usersource->type
            && $usersource->auto_agent
            && !$person->isAgent()
        ) {
            $agentChecker = App::getSystemService('agent_checker');
            /** @var $agentChecker AgentCheckerService */
            if ($agentChecker->addAgentSeat($person)) {
                $person['is_agent']  = true;
                $person['can_agent'] = true;

                return true;
            }
        }

        return false;
    }

    public static function tryUsergroupPromotion(Usersource $usersource, Person $person, $raw_info)
    {
        if ($usersource->type === Usersource::TYPE_AGENT && !$usersource->auto_agent) {
            return;
        }

        foreach ($usersource->actions as $action) {
            /* @var $action AbstractAction */
            $action->handle(App::$container, $person, $raw_info);
        }
    }
}
