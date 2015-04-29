<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
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
use Symfony\Component\OptionsResolver\OptionsResolver;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as Assert;

/**
 * @Assert\ValidTermEngineTerm()
 */
abstract class AbstractTerm implements TermInterface
{
    /**
     * The op MUST be a supported TermInterface::OP_* constant.
     *
     * @var string the op for this term
     */
    protected $op;

    /**
     * This is where all of the data of a term is stored.
     *
     * Note that default options are not stored here. We only store options
     * that were explicitely added. The only access to options is via the
     * getOptions() method (which uses OptionsResolver to resolve the options).
     *
     * @var array the defined settings
     */
    protected $options;

    /**
     * @param array $options initial settings
     * @param string $op initialize op
     */
    public function __construct(array $options = array(), $op = null)
    {
        $this->options = $options;

        if (null !== $op) {
            $this->setOp($op);
        } else {
            $this->setOp($this->getDefaultOp());
        }
    }

    /**
     * Using AbstractTerm forces the concretes to use OptionsResolver as the
     * method of defining and resolving options. Options are the core of all
     * of the terms, and we use the OptionsResolver component to help define
     * and resolve our options.
     *
     * You must use the OptionsResolver to set up option defaults, allowed
     * values, whether an option is required/optional, and so on. Please
     * refer to the symfony documentation on the OptionsResolver component.
     *
     * @param OptionsResolver $resolver
     * @return null
     */
    abstract public function configureOptions(OptionsResolver $resolver);

    /**
     * @inheritdoc
     */
    public function getOp()
    {
        return $this->op;
    }

    /**
     * @inheritdoc
     */
    public function setOp($op)
    {
        $this->op = (string)$op;
    }

    /**
     * Serialize the user-given data.
     *
     * Note that we don't serialize the resolved options.
     *
     * @return array
     */
    public function serialize()
    {
        return array(
            'op' => $this->op,
            'options' => $this->options
        );
    }

    /**
     * Unserialize the serialized data.
     *
     * @param $serialized
     */
    public function unserialize($serialized)
    {
        $this->op = $serialized['op'];
        $this->options = $serialized['options'];
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        return $resolver->resolve($this->options);
    }

    /**
     * @inheritdoc
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * @inheritdoc
     */
    public function getOption($option)
    {
        $options = $this->getOptions();

        return $options[$option];
    }

    /**
     * @inheritdoc
     */
    public function replaceOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @inheritdoc
     */
    public function setOptions(array $options)
    {
        foreach ($options as $option => $value) {
            $this->setOption($option, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function removeOption($option)
    {
        if (array_key_exists($option, $this->options)) {
            unset($this->options[$option]);
        }
    }

    /**
     * AbstractTerm lets you get the raw options that were set. This is NOT recommended to be
     * used for getting options because these raw options are NOT resolved. This method is here only for meta-info about the user-defined term options. This is deviation from the TermInterface.
     *
     * See getOptions() and getOption() to get the real options
     *
     * @return array
     * @deprecated use getOptions() instead
     */
    public function getRawOptions()
    {
        return $this->options;
    }
}
