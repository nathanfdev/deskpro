<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Sync;

use Application\DeskPRO\App;
use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonUsersourceAssoc;
use Application\DeskPRO\Entity\PhoneNumber;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\EntityRepository\TmpData as TmpDataRepo;
use Doctrine\ORM\EntityManager;
use Orb\Auth\Identity;
use Orb\Log\Logger;

/**
 * This will be offered as a service to all Syncers. It aids them by taking care of common Syncer needs.
 * A central place so we can reduce code duplication.
 */
class SyncerHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(EntityManager $em, Logger $logger = null)
    {
        $this->em     = $em;
        $this->logger = $logger;
    }

    public function getEm()
    {
        return $this->em;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function log($orb_logger_priority, $message, array $info = [])
    {
        if ($this->logger) {
            $this->logger->log('SYNC HELPER: '.$message, $orb_logger_priority, $info);
        }
    }

    public function updateOrCreatePersonWithInfo(array $user_info, Person $person = null, Usersource $usersource)
    {
        // merge in the default values on $user_info array
        if (isset($user_info['email_address'])) {
            $user_info['email'] = $user_info['email_address'];
        }
        $user_info = array_merge(
            [
                'name'            => null,
                'first_name'      => null,
                'last_name'       => null,
                'email'           => null,
                'email_confirmed' => null,
                'phone'           => null,
            ],
            $user_info
        );

        if (empty($user_info['email'])) {
            return false;
        }

        if (App::$container->getEmailAccountManager()->findAccountForEmailAddress($user_info['email'])) {
            // gateway email, don't process
            return false;
        }

        if (!$person) {
            if (!$person = $this->getPersonFromEmail($user_info['email'])) {
                $this->log(Logger::DEBUG, 'could not find a person with the email "'.$user_info['email'].'"');
                $this->log(Logger::INFO, 'creating a new person with email "'.$user_info['email'].'"', $user_info);
                $person = Person::newContactPerson(['email' => $user_info['email']]);
                $this->em->persist($person);
            }
        }

        if (!empty($user_info['first_name'])) {
            $person->setFirstName($user_info['first_name']);
        }

        if (!empty($user_info['last_name'])) {
            $person->setLastName($user_info['last_name']);
        }

        if (!empty($user_info['name'])) {
            $person->setName($user_info['name']);
        }

        if (!empty($user_info['email'])) {
            // if "$this->getPersonFromEmail($user_info['email'])" is true, we are in a potential merge situation
            // ignoring for now
            if (!$person->hasEmailAddress($user_info['email']) && !$this->getPersonFromEmail($user_info['email'])) {
                $this->log(Logger::DEBUG, 'person does not have email and no other person does either: "'.$user_info['email'].'" so we are adding it to person ID: "'.$person->getId().'"');
                $person->addEmailAddressString($user_info['email']);
            }
        }

        if (!empty($user_info['phone'])) {
            $newNumber = PhoneNumber::createEntity($user_info['phone']);
            $oldNumber = $person->getPrimaryPhoneNumber();

            if ($newNumber && (!$oldNumber || $oldNumber->getFullFormatted() !== $newNumber->getFullFormatted())) {
                $person->setPrimaryPhoneNumber($newNumber);
            }
        }

        // we only attempt an agent promotion during sync if the helpdesk has less than 500 agents
        $count = $this->em->getRepository('DeskPRO:Person')->getActiveAgentsCount();
        if ($count < 500) {
            // tries the auto-agent routine, if agent usersource (just like on login from a usersource)
            LoginProcessor::tryAutoAgent($usersource, $person);
        }

        if (!empty($user_info['picture_data'])) {
            $this->updatePictureData($person, $user_info['picture_data']);
        }

        try {
            LoginProcessor::tryUsergroupPromotion($usersource, $person, $user_info);
        } catch (\Exception $ex) {
            // actions, even if configured incorrectly shouldn't affect the auto user sync
            $this->log(Logger::ERR, sprintf(
                'Exception during login actions. Ignoring this exception. Error message: %s',
                $ex->getMessage()
            ));
        }

        // Update custom field data
        App::getSystemService('person_fields_manager')->copyUsersourceData(
            $person,
            $user_info,
            $usersource
        );

        return $person;
    }

    /**
     * @param Person $person
     * @param mixed  $pictureData
     */
    protected function updatePictureData(Person $person, $pictureData)
    {
        $filename = tempnam(dp_get_tmp_dir(), 'picture');
        $fp       = @fopen($filename, 'w');

        if (!$fp) {
            return;
        }

        @fwrite($fp, $pictureData);
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
                $filename, 'dp-source-picture.'.$mime[0], $mime[1], strlen($pictureData)
            );

            $accept = App::getContainer()->getAttachmentAccepter();
            $error  = $accept->getRestrictionSet($person->isAgent() ? 'agent' : 'user')->getError($file);
            if ($error) {
                $this->log(Logger::WARN, sprintf(
                    'Error during updating profile picture (skipping this error): %s',
                    print_r($error, true)
                ));
            } else {
                $blob = $accept->accept($file);
                if ($person->getPictureBlob()) {
                    App::getContainer()->getBlobStorage()->deleteBlobRecord(
                        $person->getPictureBlob()
                    );
                }
                $person->setPictureBlob($blob);
            }
        }

        @unlink($filename);
    }

    public function updateOrCreateAssociation(
        Usersource $usersource,
        Person $person,
        Identity $identity
    ) {
        if (!$assoc = $this->getAssociation($usersource, $person)) {
            $assoc = new PersonUsersourceAssoc();
            $this->em->persist($assoc);
        }

        $assoc->setPerson($person);
        $assoc->setUsersource($usersource);
        $assoc->setIdentity($identity->getIdentity());
        $assoc->setIdentityFriendly($identity->getFriendlyIdentity() ?: $identity->getIdentity());
        $assoc->setDateUpdated(new \DateTime());

        return $assoc;
    }

    public function persistAndFlushEntity($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    /**
     * @param Usersource  $usersource
     * @param Person|null $person_or_identifier
     *
     * @return PersonUsersourceAssoc
     */
    public function getAssociation(Usersource $usersource, $person_or_identifier = null)
    {
        if (!$person_or_identifier) {
            return;
        }

        if ($person_or_identifier instanceof Person) {
            if (!$person_or_identifier->getId()) {
                return; // not yet persisted person, cannot have an assocation yet
            }

            return $this->em->getRepository('DeskPRO:PersonUsersourceAssoc')
                ->getAssociationForPersonUsersourcePair($person_or_identifier, $usersource);
        }

        return $this->getAssoc($usersource, $person_or_identifier);
    }

    /**
     * @param Usersource $usersource
     * @param $identity
     *
     * @return PersonUsersourceAssoc|null
     */
    public function getAssoc(Usersource $usersource, $identity)
    {
        $this->log(Logger::DEBUG, 'getting assoc');

        /** @var \Application\DeskPRO\EntityRepository\PersonUsersourceAssoc $assoc_repo */
        $assoc_repo = $this->em->getRepository('DeskPRO:PersonUsersourceAssoc');
        if ($assoc = $assoc_repo->getIdentityAssociation($usersource, $identity)) {
            $this->log(Logger::DEBUG, 'SYNC HELPER: did find an existing association');

            return $assoc;
        }
        $this->log(Logger::DEBUG, 'did not find an existing association');

        return;
    }

    /**
     * @param string $email_string
     *
     * @return Person|null
     */
    public function getPersonFromEmail($email_string)
    {
        return $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email_string);
    }

    /**
     * Saves a person.
     *
     * @param Person $person
     */
    public function savePerson(Person $person, $flush = true)
    {
        $this->log(Logger::DEBUG, 'saving person');
        $this->em->persist($person);
        if ($flush) {
            $this->em->flush();
        }
    }

    /**
     * Saves the association.
     *
     * @param PersonUsersourceAssoc $association
     */
    public function saveAssociation(PersonUsersourceAssoc $association, $flush = true)
    {
        $this->log(Logger::DEBUG, 'saving association');
        $this->em->persist($association);
        if ($flush) {
            $this->em->flush();
        }
    }

    /**
     * @return TmpDataRepo
     */
    public function getTmpDataRepo()
    {
        return $this->em->getRepository('DeskPRO:TmpData');
    }
}
