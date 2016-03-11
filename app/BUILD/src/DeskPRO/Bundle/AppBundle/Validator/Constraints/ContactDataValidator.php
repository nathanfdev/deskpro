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
namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Application\DeskPRO\Entity\ContactDataAbstract;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\UrlValidator;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ContactDataValidator.
 */
class ContactDataValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ContactData) {
            throw new UnexpectedTypeException($constraint, ContactData::class);
        }
        if (!$value instanceof ContactDataAbstract) {
            throw new UnexpectedTypeException($value, ContactDataAbstract::class);
        }

        switch ($value->getContactType()) {
            case ContactDataAbstract::TYPE_ADDRESS:
                $this->validateAddress($value);
                break;
            case ContactDataAbstract::TYPE_PHONE:
                $this->validatePhone($value);
                break;
            case ContactDataAbstract::TYPE_FACEBOOK:
            case ContactDataAbstract::TYPE_LINKED_IN:
                $this->validateProfileUrl($value);
                break;
            case ContactDataAbstract::TYPE_TWITTER:
                $this->validateTwitter($value);
                break;
            case ContactDataAbstract::TYPE_INSTANT_MESSAGE:
                $this->validateInstantMessage($value);
                break;
            case ContactDataAbstract::TYPE_WEBSITE:
                $this->validateWebsite($value);
                break;
        }
    }

    /**
     * @param ContactDataAbstract $value
     */
    protected function validateAddress(ContactDataAbstract $value)
    {
    }

    /**
     * @param ContactDataAbstract $value
     */
    protected function validatePhone(ContactDataAbstract $value)
    {
    }

    /**
     * @param ContactDataAbstract $value
     */
    protected function validateTwitter(ContactDataAbstract $value)
    {
    }

    /**
     * @param ContactDataAbstract $value
     */
    protected function validateInstantMessage(ContactDataAbstract $value)
    {
        if (!in_array($value->getField2(), ContactDataAbstract::getInstantMessageTypes())) {
            $this
                ->getContext()
                ->buildViolation(Choice::NO_SUCH_CHOICE_ERROR)
                ->setCode(Choice::NO_SUCH_CHOICE_ERROR)
                ->atPath('field_2')
                ->addViolation()
            ;
        }
    }

    /**
     * @param ContactDataAbstract $value
     */
    protected function validateWebsite(ContactDataAbstract $value)
    {
        $this->validateUrl('field_1', $value);
    }

    /**
     * @param string              $property
     * @param ContactDataAbstract $value
     *
     * @return bool
     */
    protected function validateUrl($property, ContactDataAbstract $value)
    {
        $pattern           = sprintf(UrlValidator::PATTERN, implode('|', ['http', 'https']));
        $property_accessor = new PropertyAccessor();

        if (!preg_match($pattern, $property_accessor->getValue($value, $property))) {
            $this
                ->getContext()
                ->buildViolation(Url::INVALID_URL_ERROR)
                ->setCode(Url::INVALID_URL_ERROR)
                ->atPath($property)
                ->addViolation()
            ;

            return false;
        }

        return true;
    }

    /**
     * @param ContactDataAbstract $value
     */
    protected function validateProfileUrl(ContactDataAbstract $value)
    {
        if (!$this->validateUrl('field_1', $value)) {
            return;
        }

        if (!$value->getField2()) {
            $this
                ->getContext()
                ->buildViolation(Url::INVALID_URL_ERROR)
                ->setCode(Url::INVALID_URL_ERROR)
                ->atPath('field_2')
                ->addViolation()
            ;
        }
    }

    /**
     * @return ExecutionContext
     */
    protected function getContext()
    {
        return $this->context;
    }
}
