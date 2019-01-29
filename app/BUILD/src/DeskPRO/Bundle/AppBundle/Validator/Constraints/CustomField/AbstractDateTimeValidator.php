<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\CustomField;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AbstractDateTimeValidator.
 */
abstract class AbstractDateTimeValidator extends AbstractSingleValueValidator
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param TokenStorage $tokenStorage
     */
    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidators($data, AbstractCustomDefConstraint $constraint)
    {
        $validators = [];

        // Required validator
        if ($constraint->check_required && $constraint->getCustomDefOption('required', true)) {
            $validators[] = new Assert\NotBlank();
        }

        if (!$data) {
            // if no value and value is not required then skip other validators
            return $validators;
        }

        // Format validator
        $validators[] = $this->getFormatValidator();

        // Range validator
        $minRangeFormat = $constraint->custom_def->getDateMinFormat();
        $maxRangeFormat = $constraint->custom_def->getDateMaxFormat();

        if (isset($minRangeFormat)) {
            $minRangeDate = new \DateTime($minRangeFormat);
            $validators[] = new Assert\GreaterThanOrEqual([
                'value' => $minRangeDate->format($this->getFormat()),
            ]);
        }
        if (isset($maxRangeFormat)) {
            $maxRangeDate = new \DateTime($maxRangeFormat);
            $validators[] = new Assert\LessThanOrEqual([
                'value' => $maxRangeDate->format($this->getFormat()),
            ]);
        }

        // Day of week validator
        $validDow = $constraint->getCustomDefOption('date_valid_dow');
        if ($validDow) {
            $validators[] = new AppAssert\DayOfWeek([
                'choices' => $validDow,
            ]);
        }

        return $validators;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCustomDataValue(CustomDataAbstract $customData)
    {
        $value = parent::getCustomDataValue($customData);
        if (!$value) {
            return '';
        }

        try {
            $value = new \DateTime('@'.$value);
        } catch (\Exception $e) {
            try {
                $value = new \DateTime($value);
            } catch (\Exception $e) {
            }
        }

        if ($value instanceof \DateTime) {
            $token    = $this->tokenStorage->getToken();
            $user     = $token ? $token->getUser() : null;
            $timezone = new \DateTimeZone($user instanceof Person ? $user->getTimezone() : 'UTC');

            $value->setTimezone($timezone);

            return $value->format($this->getFormat());
        }

        return $value;
    }

    /**
     * @return Constraint
     */
    abstract protected function getFormatValidator();

    /**
     * @return string
     */
    abstract protected function getFormat();
}
