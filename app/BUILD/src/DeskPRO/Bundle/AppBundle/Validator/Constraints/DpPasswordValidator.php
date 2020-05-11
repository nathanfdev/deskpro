<?php



namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Application\DeskPRO\People\PasswordPolicyValidator;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DpPasswordValidator extends ConstraintValidator
{
    /**
     * @var PasswordPolicyValidator
     */
    private $pwValidator;

    /**
     * @var PortalBrandThemeLoader
     */
    private $portalBrandThemeLoader;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * Constructor.
     *
     * @param PasswordPolicyValidator $pwValidator
     * @param PortalBrandThemeLoader $portalBrandThemeLoader
     * @param BrandStack $brandStack
     */
    public function __construct(
        PasswordPolicyValidator $pwValidator,
        PortalBrandThemeLoader $portalBrandThemeLoader,
        BrandStack $brandStack
    ) {
        $this->pwValidator             = $pwValidator;
        $this->portalBrandThemeLoader  = $portalBrandThemeLoader;
        $this->brandStack              = $brandStack;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof DpPassword) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\DpPassword');
        }

        $person = $constraint->person;
        if (!$this->pwValidator->checkPassword($value, $person, $error)) {
            $policy = $this->pwValidator->getPolicy($person);

            if ($this->isHelpcenter()) {
                $errorPhrase = 'helpcenter.forms.error_password_'.$error;
            } else {
                $errorPhrase = 'portal.forms.error_password_'.$error;
            }
            $errorParams = [];

            switch ($error) {
                case 'min_length':
                    $errorParams['count'] = $policy->min_length;

                    break;
                case 'require_num_uppercase':
                    $errorParams['count'] = $policy->require_num_uppercase;

                    break;
                case 'require_num_lowercase':
                    $errorParams['count'] = $policy->require_num_lowercase;

                    break;
                case 'require_num_number':
                    $errorParams['count'] = $policy->require_num_number;

                    break;
                case 'require_num_symbol':
                    $errorParams['count'] = $policy->require_num_symbol;

                    break;
                case 'forbid_reuse':
                    $errorParams['count'] = $policy->forbid_reuse;

                    break;
            }

            $this->buildViolation($errorPhrase)
                    ->setParameters($errorParams)
                    ->addViolation();
        }
    }

    private function isHelpcenter()
    {
        return $this->portalBrandThemeLoader->getPortalBrandTheme($this->brandStack->getActive()->getBrand())->getActiveThemeSet()->getThemeId() === 'helpcenter';
    }
}
