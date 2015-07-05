<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 */

namespace DeskPRO\Bundle\PortalBundle\Person;


use DeskPRO\Bundle\AppBundle\DataService\EmailDataService;
use DeskPRO\Bundle\PortalBundle\EmailSender\PortalEmailSender;
use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Routing\PortalRouter;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PersonValidator
{
    const TYPE_EMAIL = 'email';
    const TYPE_EMAIL_PRIMARY = 'email-primary';

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

    public function __construct(EntityManager $em, PortalRouter $router, EmailDataService $email_data, PortalEmailSender $portal_email_sender)
    {
        $this->router = $router;
        $this->email_data = $email_data;
        $this->portal_email_sender = $portal_email_sender;
        $this->em = $em;
    }

    public function validateEmail($email, $flush = true)
    {
        $email = $this->email_data->getEmail($email);

        $email->is_own_validated = true;
        $email->is_validated = true;

        $this->em->persist($email);

        if ($flush) {
            $this->em->flush($email);
        }
    }

    /**
     * If appropriate, this method will give you a link for the user to click to validate the object
     *
     * @param $type
     * @param $email_or_id
     * @param null $type_id
     * @return string|null the url if it can be made
     */
    public function getEmailLink($type, $email_or_id, $type_id = null)
    {
        if (!$person_email = $this->email_data->getEmail($email_or_id)) {
            return null;
        }

        switch ($type) {
            case PersonValidator::TYPE_EMAIL:
                return $this->router->generate(
                    'portal_validation',
                    array(
                        'object_type' => self::TYPE_EMAIL,
                        'email_id' => $person_email->getId()
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            case PersonValidator::TYPE_EMAIL_PRIMARY:
                return $this->router->generate(
                    'portal_validation',
                    array(
                        'object_type' => self::TYPE_EMAIL_PRIMARY,
                        'email_id' => $person_email->getId()
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
        }

        return null;
    }

    /**
     * Sends a link to the user with the verify link. Note that this might fail (if for example the
     * email id you send in doesnt exist), so check the bool return.
     *
     * @param $type
     * @param $email_or_id
     * @param $type_id
     * @return bool true if sent, false if not
     */
    public function doResendLink($type, $email_or_id, $type_id = null)
    {
        if (!$person_email = $this->email_data->getEmail($email_or_id)) {
            return false;
        }

        switch ($type) {
            case PersonValidator::TYPE_EMAIL:
                $this->portal_email_sender->sendEmailConfirmationEmail($person_email);
                return true;
            case PersonValidator::TYPE_EMAIL_PRIMARY:
                $this->portal_email_sender->sendEmailConfirmationEmail($person_email, true);
                return true;
        }

        return false;
    }

    /**
     * Sends a link to the user with the verify link. Note that this might fail (if for example the
     * email id you send in doesnt exist), so check the bool return.
     *
     * @param $type
     * @param $email_or_id
     * @param $type_id
     * @return bool true if sent, false if not
     */
    public function getResendLink($type, $email_or_id, $type_id = null)
    {
        if (!$person_email = $this->email_data->getEmail($email_or_id)) {
            return false;
        }

        switch ($type) {
            case PersonValidator::TYPE_EMAIL:
                return $this->router->generate(
                    'portal_send_validation',
                    array(
                        'email_id' => $person_email->getId(),
                        'object_type' => self::TYPE_EMAIL
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            case PersonValidator::TYPE_EMAIL_PRIMARY:
                return $this->router->generate(
                    'portal_send_validation',
                    array(
                        'email_id' => $person_email->getId(),
                        'object_type' => self::TYPE_EMAIL_PRIMARY
                    ),
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
        }

        return false;
    }
}
