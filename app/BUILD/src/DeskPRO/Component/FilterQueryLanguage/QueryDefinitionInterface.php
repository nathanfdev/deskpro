<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Component\FilterQueryLanguage;

/**
 * This helps validate a query by allowing you to specify constraints.
 * For example, certain functions might only make sense with certain operators or fields.
 */
interface QueryDefinitionInterface
{
    /**
     * Check if a field is a valid field.
     *
     * @param string $identity
     *
     * @return string[]
     */
    public function validateField($identity);

    /**
     * Check if a variable is valid.
     *
     * @param string $varId
     *
     * @return string[]
     */
    public function validateVariable($varId);

    /**
     * Check if a function call is a valid function call.
     *
     * @param string   $name
     * @param string[] $params
     * @param string   $op
     * @param string   $fieldId
     *
     * @return string[]
     */
    public function validateFunctionCall($name, $params, $op, $fieldId);

    /**
     * Get the type of a field: STRING, NUMERIC (int or float), INT, FLOAT, DATE.
     *
     * Append `[]` to a field to indicate it's a list of that type. E.g. STRING[].
     *
     * Return an empty string for no type assertion.
     *
     * @param string $identity
     *
     * @return string
     */
    public function fieldType($identity);
}
