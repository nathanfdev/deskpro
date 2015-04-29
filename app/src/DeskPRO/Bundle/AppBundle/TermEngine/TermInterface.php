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

namespace DeskPRO\Bundle\AppBundle\TermEngine;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface TermInterface
{
    const OP_NOOP = 'noop';
    const OP_IS = 'is';
    const OP_NOT = 'not';
    const OP_LT = 'lt';
    const OP_GT = 'gt';
    const OP_LTE = 'lte';
    const OP_GTE = 'gte';
    const OP_RANGE = 'range';
    const OP_NOT_RANGE = 'not_range';
    const OP_HAS = 'has';
    const OP_NOT_HAS = 'not_has';
    const OP_EXISTS = 'exists';
    const OP_NOT_EXISTS = 'not_exists';
    const OP_OR = 'or';
    const OP_AND = 'and';

    /**
     * Get the op code. This will be a TermInterface::OP_* constant string.
     *
     * @return string
     */
    public function getOp();

    /**
     * Set the op code. Use TermInterface::OP_* constants.
     *
     * @param string $op the op code
     */
    public function setOp($op);

    /**
     * Returns an array of all possible OP_ codes that this term supports
     *
     * @return array
     */
    public function getSupportedOps();

    /**
     * Gets the default OP_ code for this term.
     *
     * Immediately after instantiating the term, getOp() should return this value
     * unless a constructor argument exists that allows an override.
     *
     * This OP must be a supported OP in getSupportedOps()
     *
     * @return string
     */
    public function getDefaultOp();

    /**
     * Get an array of all resolved options for this term.
     *
     * @return array the resolved settings
     */
    public function getOptions();

    /**
     * Set a single option.
     *
     * @param string $option option name
     * @param mixed $value the scalar value of the option
     */
    public function setOption($option, $value);

    /**
     * Get a RESOLVED option value.
     *
     * Note that it will actually resolve the options before passing you your option.
     *
     * @param string $option option name
     * @return mixed
     */
    public function getOption($option);

    /**
     * Remove a single option.
     *
     * @param string $option the option name.
     */
    public function removeOption($option);

    /**
     * Shortcut method to set many options at once.
     *
     * See TermInterface::replaceOptions to also delete other options.
     *
     * @param array $options the options (key = option name, value = option value)
     */
    public function setOptions(array $options);

    /**
     * Remove all currently set options and set the passed array as the new options.
     *
     * See TermInterface::setOptions if you don't want to remove options.
     *
     * @param array $options the new options (key = option name, value = option value)
     */
    public function replaceOptions(array $options);

    /**
     * An array that represents the term for seializing.
     *
     * The returned array will be passed to unserialize() directly after re-constructing
     * the term to recreate it. Almost always this will just be the array of options for the term.
     *
     * @return array
     */
    public function serialize();

    /**
     * Reconstruct the object from the output of serialize().
     *
     * @param $serialized_form
     * @return void
     */
    public function unserialize($serialized_form);
}
