<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

class DepartmentTerm extends AbstractTerm
{
    protected $op = TermInterface::OP_IS;

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'department_ids' => array(),
            )
        );

        $resolver->setAllowedTypes(
            array(
                'department_ids' => 'array'
            )
        );
    }

    public function doValidate(ExecutionContextInterface $validator_context)
    {
        // note we should not us the $options property directly when validating,
        // because the resolver will give the real options we care about after
        // all is said and done

        $dep_ids = $this->getOption('department_ids');
        $op = $this->getOp();

        if (!in_array($op, array(TermInterface::OP_IS, TermInterface::OP_NOT))) {

            // here is how to add an error to the term:
            $validator_context->buildViolation(('unsupported operation'))->atPath('op')->addViolation();

        }

        // note that to use external services as validators, we can do that
        // by adding a @Assert\Callback on the class itself.
    }

    /**
     * @Assert\Callback
     *
     * NOTE: This will be abstract, so each term wont need this
     *
     * @param ExecutionContextInterface $validator_context
     */
    public function validate(ExecutionContextInterface $validator_context)
    {
        // TODO: this should be in the abstract term, and the reason we need it is
        // because we use OptionsResolver Component to get options from the term.
        // if the OptionsResolver throws an exception, we need to turn that
        // into an error. Note that these are unlikely, so can probably just be
        // a 1-liner error

        // when the validator component attempts to validate this term, this
        // method is called, so you can set as many errors as you'd like.

        try {
            $this->doValidate($validator_context);
        } catch (MissingOptionsException $e) {
            // options resolver can throw a few more exceptions, check the namespace
        } catch (InvalidOptionsException $e) {
            // options resolver can throw a few more exceptions, check the namespace
        } catch (\InvalidArgumentException $e) {
            // we might be ale to get away with just this base \InvalidArgumentException
            // if we don't use the exception type to make the error string
        }
    }
}
