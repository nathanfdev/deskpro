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

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Voice;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount as VoiceAccountEntity;
use DeskPRO\Bundle\AppBundle\Twilio\TwilioAdapter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class VoiceAccountValidator.
 */
class VoiceAccountValidator extends ConstraintValidator
{
    /**
     * @var TwilioAdapter
     */
    private $twilioAdapter;

    /**
     * Constructor.
     *
     * @param TwilioAdapter $twilioAdapter
     */
    public function __construct(TwilioAdapter $twilioAdapter)
    {
        $this->twilioAdapter = $twilioAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof VoiceAccount) {
            throw new UnexpectedTypeException($constraint, VoiceAccount::class);
        }

        if (!$value instanceof VoiceAccountEntity) {
            throw new UnexpectedTypeException($value, VoiceAccountEntity::class);
        }

        // don't check if not all credentials were provided
        if (!$value->getAccountSid() || !$value->getAuthToken()) {
            return;
        }

        if (!$this->twilioAdapter->getAccount($value)) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(VoiceAccount::INVALID_ACCOUNT_CREDENTIALS)
                ->addViolation()
            ;
        }
    }
}
