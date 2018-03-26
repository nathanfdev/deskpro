<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;

/**
 * Defines a Term that can be used in the TermEngine.
 *
 * A Term represents an assertion of some kind, and is configurable using a combination
 * of an "OP" and a set of "Options".
 *
 * - An "OP" is an op code used by Term Engine compilers; can be thought of as a verb.
 * - The "Options" are term specific key/value pairs representing specific parameters for the Term.
 *
 * A term may mutate options as they are set, and may provide default values for options. Therefore, you should
 * always use accessor methods [getOption(name), or getOptions()] to access the options of a term.
 */
interface TermInterface
{
    const OP_NOOP       = 'noop';
    const OP_IS         = 'is';
    const OP_NOT        = 'not';
    const OP_LT         = 'lt';
    const OP_GT         = 'gt';
    const OP_LTE        = 'lte';
    const OP_GTE        = 'gte';
    const OP_RANGE      = 'range';
    const OP_NOT_RANGE  = 'not_range';
    const OP_HAS        = 'has';
    const OP_NOT_HAS    = 'not_has';
    const OP_EXISTS     = 'exists';
    const OP_NOT_EXISTS = 'not_exists';
    const OP_OR         = 'or';
    const OP_AND        = 'and';

    /**
     * @return TermOptionsResolver
     */
    public static function getOptionsResolver();

    /**
     * Set the OP code. Must be a TermInterface::OP_* constant.
     *
     * @param string $op the op code
     */
    public function setOp($op);

    /**
     * Get the OP code. This will be a TermInterface::OP_* constant.
     *
     * @return string
     */
    public function getOp();

    /**
     * Returns an array of all possible TermInterface::OP_* codes that this term supports.
     *
     * @return array
     */
    public function getSupportedOps();

    /**
     * Gets the default TermInterface::OP_* code for this term.
     *
     * Immediately after instantiating the term, getOp() should return this value
     * unless a constructor argument exists that allows an override.
     *
     * This OP must be a supported TermInterface::OP_* code in getSupportedOps()
     *
     * @return string
     */
    public function getDefaultOp();

    /**
     * Set a single option.
     *
     * @param string $option option name
     * @param mixed  $value  the scalar value of the option
     */
    public function setOption($option, $value);

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
     * Remove a single option.
     *
     * @param string $option the option name
     */
    public function removeOption($option);

    /**
     * Get an array of all RESOLVED options for this term.
     *
     * @return array the resolved settings
     */
    public function getOptions();

    /**
     * Checks if an option exists within the collection.
     *
     * @param string $options option name
     *
     * @return bool
     */
    public function hasOption($option);

    /**
     * Get a RESOLVED option value.
     *
     * Note that it will actually resolve the options before passing you your option.
     *
     * @param string $option option name
     *
     * @return mixed
     */
    public function getOption($option);

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
     */
    public function unserialize($serialized_form);
}
