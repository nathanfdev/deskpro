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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\Person;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonEmailValidating;
use DeskPRO\Bundle\AppBundle\DataService\EmailDataService;
use DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackDataService;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\EmailSender\PortalEmailSender;
use DeskPRO\Bundle\PortalBundle\Routing\PortalRouter;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PersonValidator
{
    const TYPE_EMAIL         = 'email';
    const TYPE_EMAIL_PRIMARY = 'email-primary';
    const TYPE_FEEDBACK      = 'feedback';

    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var PortalRouter
     */
    private $router;

    /**
     * @var EmailDataService
     */
    private $email_data;

    /**
     * @var \DeskPRO\Bundle\PortalBundle\EmailSender\PortalEmailSender
     */
    private $portal_email_sender;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var FeedbackDataService
     */
    private $feedback_data;

    public function __construct(EntityManager $em, PortalRouter $router, EmailDataService $email_data, PortalEmailSender $portal_email_sender, FeedbackDataService $feedback_data)
    {
        $this->router              = $router;
        $this->email_data          = $email_data;
        $this->portal_email_sender = $portal_email_sender;
        $this->em                  = $em;
        $this->feedback_data       = $feedback_data;
    }

    /**
     * @param int|PersonEmail|PersonEmailValidating $email
     * @param bool                                  $is_validating
     * @param bool                                  $flush
     */
    public function validateEmail($email, $is_validating = false, $flush = true)
    {
        if (!$validating_email = $this->findEmail($email, $is_validating)) {
            return;
        }

        if ($validating_email instanceof PersonEmailValidating) {
            $person          = $validating_email->getPerson();
            $validated_email = new PersonEmail();
            $validated_email->setPerson($person);
            $validated_email->setEmail($validating_email->getEmail());
            $this->em->persist($validated_email);
            $person->addEmail($validated_email);
            $this->em->remove($validating_email);
        } else {
            $validated_email = $validating_email;
        }

        $validated_email->is_validated     = true;
        $validated_email->date_validated   = new \DateTime();

        $this->em->persist($validated_email);

        if ($flush) {
            $this->em->flush($validated_email);
        }
    }

    /**
     * Given an email ID and a feedback ID, mark them as validated.
     *
     * @param $email_id
     * @param $feedback_id
     *
     * @return bool
     */
    public function validateFeedback($email_id, $feedback_id)
    {
        if (!$feedback = $this->feedback_data->getItem($feedback_id)) {
            return false;
        }

        $secondary = false;
        if (!$email = $this->email_data->getEmail($email_id)) {
            $secondary = true;
        } else {
            if ($email->getPerson() !== $feedback->getPerson()) {
                $secondary = true;
            }
        }

        if ($secondary && !$email = $this->email_data->getValidatingEmail($email_id)) {
            // it should be a secondard email, but none were found, giving up
            return false;
        }

        // found an email, and we know if its still validating or already a PersonEmail
        // validate it
        $this->validateEmail($email, $secondary);

        $feedback->setStatusCode(Feedback::STATUS_ACTIVE);
        $this->em->persist($feedback);
        $this->em->flush($feedback);
    }

    /**
     * If appropriate, this method will give you a link for the user to click to validate the object.
     *
     * @param string                                $type
     * @param int|PersonEmail|PersonEmailValidating $email_or_id
     * @param null                                  $type_id
     * @param bool                                  $is_email_validating only true if its a SECONDARY email that was added (not primary)
     *
     * @return string|null the url if it can be made
     */
    public function getEmailLink($type, $email_or_id, $type_id = null, $is_email_validating = false)
    {
        if (!$person_email = $this->findEmail($email_or_id, $is_email_validating)) {
            return;
        }

        switch ($type) {
            case self::TYPE_EMAIL:
                if (!$person_email instanceof PersonEmailValidating) {
                    throw new \InvalidArgumentException('TYPE_EMAIL expects a PersonEmailValidating');
                }

                return $this->router->generate(
                    'portal_validation',
                    array(
                        'object_type' => self::TYPE_EMAIL,
                        'email_id'    => $person_email->getId(),
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            case self::TYPE_EMAIL_PRIMARY:
                if (!$person_email instanceof PersonEmail) {
                    throw new \InvalidArgumentException('TYPE_EMAIL_PRIMARY expects a PersonEmail');
                }

                return $this->router->generate(
                    'portal_validation',
                    array(
                        'object_type' => self::TYPE_EMAIL_PRIMARY,
                        'email_id'    => $person_email->getId(),
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            case self::TYPE_FEEDBACK:

                return $this->router->generate(
                    'portal_validation',
                    array(
                        'object_type' => self::TYPE_FEEDBACK,
                        'email_id'    => $person_email->getId(),
                        'object_id'   => $type_id,
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
        }

        return;
    }

    /**
     * Sends a link to the user with the verify link. Note that this might fail (if for example the
     * email id you send in doesnt exist), so check the bool return.
     *
     * @param string   $type
     * @param int      $email_or_id
     * @param int|null $type_id
     * @param bool     $is_email_validating only true if its a SECONDARY email that was added (not primary)
     *
     * @return bool true if sent, false if not
     */
    public function doResendLink($type, $email_or_id, $type_id = null, $is_email_validating = false)
    {
        if (!$person_email = $this->findEmail($email_or_id, $is_email_validating)) {
            return;
        }

        switch ($type) {
            case self::TYPE_EMAIL:
                // TODO: validation
                //$this->portal_email_sender->sendEmailConfirmationEmail($person_email);
                return true;

            case self::TYPE_EMAIL_PRIMARY:
                // TODO: validation
                //$this->portal_email_sender->sendEmailConfirmationEmail($person_email, true);
                return true;

            case self::TYPE_FEEDBACK:
                $feedback = $this->feedback_data->getItem($type_id);
                $this->portal_email_sender->sendFeedbackValidationLink($feedback);

                return true;
        }

        return false;
    }

    /**
     * Generates a URL that when clicked will re-send the validation link to the user.
     *
     * @param string                                $type
     * @param int|PersonEmail|PersonEmailValidating $email_or_id
     * @param int|null                              $type_id
     * @param bool                                  $is_email_validating only true if its a SECONDARY email that was added (not primary)
     *
     * @return string|null the absolute URL that when clicked will re-send the email to the user
     */
    public function getResendLink($type, $email_or_id, $type_id = null, $is_email_validating = false)
    {
        if (!$person_email = $this->findEmail($email_or_id, $is_email_validating)) {
            return;
        }

        switch ($type) {
            case self::TYPE_EMAIL:
                if (!$person_email instanceof PersonEmailValidating) {
                    throw new \InvalidArgumentException('TYPE_EMAIL expects a PersonEmailValidating');
                }

                return $this->router->generate(
                    'portal_send_validation',
                    array(
                        'email_id'    => $person_email->getId(),
                        'object_type' => self::TYPE_EMAIL,
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

            case self::TYPE_EMAIL_PRIMARY:
                if (!$person_email instanceof PersonEmail) {
                    throw new \InvalidArgumentException('TYPE_EMAIL_PRIMARY expects a PersonEmail');
                }

                return $this->router->generate(
                    'portal_send_validation',
                    array(
                        'email_id'    => $person_email->getId(),
                        'object_type' => self::TYPE_EMAIL_PRIMARY,
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

            case self::TYPE_FEEDBACK:
                return $this->router->generate(
                    'portal_send_validation',
                    array(
                        'email_id'    => $person_email->getId(),
                        'object_type' => self::TYPE_FEEDBACK,
                        'object_id'   => $type_id,
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
        }

        return;
    }

    /**
     * @param int|PersonEmail|PersonEmailValidating $email_or_id
     * @param bool                                  $is_email_validating
     *
     * @return PersonEmail|PersonEmailValidating|null
     */
    protected function findEmail($email_or_id, $is_email_validating)
    {
        if (!$is_email_validating) {
            // a normal PersonEmail
            if (!$person_email = $this->email_data->getEmail($email_or_id)) {
                return;
            }

            return $person_email;
        }

        // in some instances (when a person adds a secondary email) we get a PersonEmailValidating
        if (!$person_email = $this->email_data->getValidatingEmail($email_or_id)) {
            return;
        }

        return $person_email;
    }
}
