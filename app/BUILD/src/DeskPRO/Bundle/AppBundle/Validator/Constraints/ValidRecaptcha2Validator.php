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

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\ReCaptchaType;
use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
