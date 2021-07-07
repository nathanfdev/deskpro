<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Captcha;

use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\ReCaptchaType;
use DeskPRO\Bundle\AppBundle\Settings\Model\AntiAbuse\CaptchaAntiAbuseSettings;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use ReCaptcha\ReCaptcha;
use ReCaptcha\RequestMethod\CurlPost;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ValidRecaptcha2Validator.
 */
class ValidRecaptcha2Validator extends ConstraintValidator
{
    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var RequestStack
     */
    private $request_stack;

    /**
     * Constructor.
     *
     * @param BrandStack $brand_stack
     * @param RequestStack                                 $request_stack
     */
    public function __construct(BrandStack $brand_stack, RequestStack $request_stack)
    {
        $this->brand_stack   = $brand_stack;
        $this->request_stack = $request_stack;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $recaptchaVersion = $this->getRecaptchaVersion();
        $recaptcha        = new ReCaptcha($this->getSecretKey(), new CurlPost());
        $request          = $this->request_stack->getMasterRequest();
        $recaptcha_value  = $request->get('g-recaptcha-response');

        $response = $recaptcha->verify($recaptcha_value, $request->getClientIp());

        if ($recaptchaVersion === CaptchaAntiAbuseSettings::RecaptchaVersion3 && $response->isSuccess() && $response->getScore() <= 0.5) {
            $this->context
                ->buildViolation($constraint->message)
                ->setCode(HcValidRecaptcha2::CAPTCHA_ERROR)
                ->addViolation();
        }

        if (!$response->isSuccess()) {
            $this->context
                ->buildViolation($constraint->message)
                ->setCode(HcValidRecaptcha2::CAPTCHA_ERROR)
                ->addViolation();
        }
    }

    /**
     * @return mixed
     */
    protected function getSecretKey()
    {
        $setting_secret = $this->brand_stack->getActive()->getSetting('core.recaptcha2_secret_key');

        if (!empty($setting_secret)) {
            return $setting_secret;
        }

        return ReCaptchaType::getCloudRecaptchaSecret();
    }

    /**
     * @return mixed
     */
    protected function getRecaptchaVersion()
    {
        $version = $this->brand_stack->getActive()->getSetting('core.recaptcha_version');

        if (is_numeric($version)) {
            return $version;
        }

        return ReCaptchaType::getCloudRecaptchaVersion();
    }
}
