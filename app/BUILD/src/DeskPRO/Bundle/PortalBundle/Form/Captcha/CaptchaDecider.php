<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\PortalBundle\Form\Captcha;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\PasswordResetAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\RegistrationAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitCommentAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitFeedbackAbuseCheck;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\SubmitTicketAbuseCheck;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * The general pattern here is that if the user is a guest, we determine showing a captcha or not via a setting
 * for the type of action the user is doing (ie. if user.registration_captcha is on and it's a guest, we show the
 * captcha). Sometimes this setting may be off. In those cases, we still check with the anti-abuse system to check vs .
 * IP. Therefore, it is always possible to see a captcha even if you disable it in settings, because the AntiAbuse system
 * has precedence.
 */
class CaptchaDecider
{
    const CAPTCHA_GUESTS   = 'guests';
    const CAPTCHA_EVERYONE = 'everyone';

    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var AuthorizationChecker
     */
    private $authorization_checker;

    /**
     * @var AntiAbuse
     */
    private $anti_abuse;

    /**
     * @var TokenStorage
     */
    private $token_storage;

    /**
     * @var RequestStack
     */
    private $request_stack;

    public function __construct(
        BrandStack $brand_stack,
        AuthorizationChecker $authorization_checker,
        TokenStorage $token_storage,
        RequestStack $request_stack,
        AntiAbuse $anti_abuse
    ) {
        $this->brand_stack           = $brand_stack;
        $this->authorization_checker = $authorization_checker;
        $this->token_storage         = $token_storage;
        $this->request_stack         = $request_stack;
        $this->anti_abuse            = $anti_abuse;
    }

    public function shouldRequireFeedbackCaptchaForCurrentPerson()
    {
        return $this->shouldRequireCaptcha(AntiAbuse::ACTION_SUBMIT_FEEDBACK, 'user.captcha.feedback');
    }

    public function shouldRequireCommentCaptchaForCurrentPerson()
    {
        return $this->shouldRequireCaptcha(AntiAbuse::ACTION_SUBMIT_COMMENT, 'user.captcha.comments');
    }

    public function shouldRequireRegistrationCaptchaForCurrentPerson()
    {
        return $this->shouldRequireCaptcha(AntiAbuse::ACTION_REGISTER, 'user.captcha.register');
    }

    public function shouldRequireTicketCaptchaForCurrentPerson()
    {
        return $this->shouldRequireCaptcha(AntiAbuse::ACTION_SUBMIT_TICKET, 'user.captcha.tickets');
    }

    public function shouldRequireForgotPasswordCaptchaForCurrentPerson()
    {
        return $this->shouldRequireCaptcha(AntiAbuse::ACTION_RESET_PASSWORD, 'user.captcha.register');
    }

    protected function shouldRequireCaptcha($where, $setting_name)
    {
        // We might not have a request at all (e.g. during tests)
        if (!$this->request_stack->getMasterRequest()) {
            return false;
        }

        $setting = $this->getBrandSetting($setting_name);

        if ($this->getCurrentPerson()->isAgent() || $this->getCurrentPerson()->isAdmin()) {
            return false;
        }
        if ($setting) {
            if ($setting === self::CAPTCHA_EVERYONE) {
                return true;
            }
            if (!$this->authorization_checker->isGranted('ROLE_USER')) {
                if ($setting === self::CAPTCHA_GUESTS) {
                    return true;
                }
            }
        }

        switch ($where) {
            case AntiAbuse::ACTION_REGISTER:
                $check = new RegistrationAbuseCheck($this->getCurrentPerson(), $this->getRequestIp());
                break;
            case AntiAbuse::ACTION_SUBMIT_FEEDBACK:
                $check = new SubmitFeedbackAbuseCheck($this->getCurrentPerson(), $this->getRequestIp());
                break;
            case AntiAbuse::ACTION_SUBMIT_TICKET:
                $check = new SubmitTicketAbuseCheck($this->getCurrentPerson(), $this->getRequestIp());
                break;
            case AntiAbuse::ACTION_SUBMIT_COMMENT:
                $check = new SubmitCommentAbuseCheck($this->getCurrentPerson(), $this->getRequestIp());
                break;
            case AntiAbuse::ACTION_RESET_PASSWORD:
                $check = new PasswordResetAbuseCheck($this->getCurrentPerson(), $this->getRequestIp());
                break;
            default:
                throw new \InvalidArgumentException('CaptchaDecider does not support $where = "'.$where.'"');
        }

        $check->markAsCheckOnly();
        $this->anti_abuse->check($check);

        return $check->isCaptchaRecommended();
    }

    public function getBrandSetting($setting, $default = null)
    {
        return $this->brand_stack->getActive()->getSetting($setting, $default);
    }

    private function getRequestIp()
    {
        return $this->request_stack->getMasterRequest()->getClientIp();
    }

    protected function getCurrentPerson()
    {
        $user = null;
        if (null !== $token = $this->token_storage->getToken()) {
            if (is_object($person = $token->getUser())) {
                if ($person instanceof Person) {
                    return $person;
                }
            }
        }

        return new PersonGuest();
    }
}
