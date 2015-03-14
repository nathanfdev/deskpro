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
    const OP_BETWEEN = 'between';
    const OP_NOT_BETWEEN = 'not_between';
    const OP_CONTAINS = 'contains';
    const OP_NOT_CONTAINS = 'not_contains';
    const OP_IS_REGEX = 'is_regex';
    const OP_NOT_REGEX = 'not_regex';
    const OP_IS_SET = 'is_set';
    const OP_NOT_SET = 'not_set';
    const OP_CHANGED = 'changed';
    const OP_NOT_CHANGED = 'changed';
    const OP_CHANGED_TO = 'changed_to';
    const OP_NOT_CHANGED_TO = 'not_changed_to';
    const OP_CHANGED_FROM = 'changed_from';
    const OP_NOT_CHANGED_FROM = 'not_changed_from';
    const OP_TOUCHED = 'touched';
    const OP_NOT_TOUCHED = 'not_touched';
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
}
