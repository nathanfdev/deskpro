<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Captcha;

use Application\DeskPRO\Entity\TmpData;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ApiCaptchaValidator.
 */
class ApiCaptchaValidator extends ConstraintValidator
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ApiCaptcha) {
            throw new UnexpectedTypeException($constraint, ApiCaptcha::class);
        }

        // don't validate if empty value
        if (empty($value['token']) || empty($value['phrase'])) {
            return;
        }

        $tmpData = $this->em->getRepository(TmpData::class)->findOneBy([
            'name' => 'api_captcha.'.$value['token'],
        ]);

        if (!$tmpData || $tmpData->getData('phrase') !== $value['phrase']) {
            /** @var \Symfony\Component\Validator\Context\ExecutionContext $context */
            $context = $this->context;
            $context
                ->buildViolation($constraint->message)
                ->setCode(ApiCaptcha::CAPTCHA)
                ->addViolation()
            ;

            // remove outdated captcha value on failure
            if ($tmpData) {
                $this->em->remove($tmpData);
                $this->em->flush();
            }
        }
    }
}
