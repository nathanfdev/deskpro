<?php

namespace DeskPRO\Bundle\AppBundle\Security;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Settings\PortalSettingsResolver;
use Doctrine\ORM\EntityManager;

/**
 * Class LimitEmailDomainsChecker.
 */
class LimitEmailDomainsChecker
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var PortalSettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param EntityManager          $em
     * @param PortalSettingsResolver $settingsResolver
     */
    public function __construct(EntityManager $em, PortalSettingsResolver $settingsResolver)
    {
        $this->em               = $em;
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * @param Person $person
     *
     * @return bool
     */
    public function checkPerson(Person $person)
    {
        // ignore agents
        if ($person->isActiveAgent()) {
            return true;
        }

        foreach ($person->getEmails() as $email) {
            if ($this->checkEmail($email->getEmail())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public function checkEmail($email)
    {
        // ignore agents
        $person = $this->em->getRepository(Person::class)->findOneByEmail($email);
        if ($person && $person->isActiveAgent()) {
            return true;
        }

        $settings = $this->settingsResolver->getGeneralSettings();
        if (!$settings->isLimitEmailDomains()) {
            return true;
        }

        $patterns = $settings->getLimitEmailDomainsPatterns();
        $patterns = explode(',', $patterns);

        foreach ($patterns as $pattern) {
            if ($this->validateEmailDomain($pattern, $email)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $pattern
     * @param $email
     *
     * @return bool
     */
    public function validateEmailDomain($pattern, $email)
    {
        $regex = trim($pattern);
        $regex = str_replace('.', '\.', $regex);
        $regex = str_replace('*', '.*', $regex);
        $regex = "/@$regex$/";

        return (bool) preg_match($regex, $email);
    }
}
