<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Captcha;

use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\ReCaptchaType;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use ReCaptcha\ReCaptcha;
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
     * @param \DeskPRO\Bundle\BrandBundle\Brand\BrandStack $brand_stack
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
        $recaptcha = new ReCaptcha($this->getSecretKey());

        $request         = $this->request_stack->getMasterRequest();
        $recaptcha_value = $request->get('g-recaptcha-response');

        /* @var \ReCaptcha\Response $response */
        $response = $recaptcha->verify($recaptcha_value, $request->getClientIp());
        if (!$response->isSuccess()) {
            $this->context->addViolation($constraint->message);
        }
    }

    /**
     * @return mixed
     */
    protected function getSecretKey()
    {
        $setting_secret = $this->brand_stack->getActive()->getSetting('core.recaptcha2_secret_key');

        if (strlen($setting_secret) > 0) {
            return $setting_secret;
        }

        return ReCaptchaType::getCloudRecaptchaSecret();
    }
}
