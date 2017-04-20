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

/**
 * DeskPRO.
 */

namespace Orb\Auth\Adapter;

use Orb\Auth\Result;
use Orb\Log\Logger;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParserCache\ArrayParserCache;

/**
 * All Orb\Auth\Adapter's should extend this instead of implementing AdapterInterface on their own.
 *
 * This class takes control of the authenticate() method and delegates to an abstract method to handle it.
 *
 * You can extend the functionality of authenticate() of all children in the authenticate()
 * method of this class.
 */
abstract class PluginAdapter implements AdapterInterface
{
    protected $filter_expression_text;
    private $_exp_lang;

    /**
     * Authenticate a user.
     *
     * @return Result
     */
    public function authenticate()
    {
        $result = $this->doAuthenticate();

        // Any usersource that returns a valid RESULT from their authenticate() method
        // will be checked againt the "filter" set by an admin. It uses the result's
        // Identity->getRawData() as the array the filter checks against.

        if ($result->isValid()) {
            $raw_info = $result->getIdentity()->getRawData();
            if (!$this->doesRawInfoPassFilter($raw_info)) {
                // filter failed. return a failure.
                return new Result(
                    Result::FAILURE,
                    $result->getIdentity(),
                    ['error_code'         => 'failed_filter',
                          'error_message' => 'Did not pass the filter requirement "'.$this->filter_expression_text.'"',
                    ]
                );
            }
        }

        return $result;
    }

    /**
     * @param $raw_info array an array of raw info from the usersource (from Identity->getRawInfo(), the same that we show if you "Test" a usersource in the admin interface), and
     *
     * @return bool true if the admin-provided expression "filter" is satisfied with the $raw_info
     */
    public function doesRawInfoPassFilter($raw_info)
    {
        if (!$this->filter_expression_text
            || strlen(trim($this->filter_expression_text)) === 0) {
            return true; // everything passes a blank filter
        }

        $e = null;
        try {
            $expression_language = $this->_getExpressionLang();
            // we supress this call because it can produce E_NOTICE's even though
            // we catch the exception.
            if (@$expression_language->evaluate($this->filter_expression_text, [
                'user' => $raw_info,
            ])) {
                return true; // successfully passed
            }
        } catch (\Exception $e) {

            // proceed to fail
        }

        $this->_logFilterFailure($raw_info, $e);

        return false;
    }

    private function _logFilterFailure($raw_info, \Exception $e = null)
    {
        if (method_exists($this, 'getLogger')) {
            $logger = $this->getLogger();
            if ($logger) {
                $logger->log(
                    sprintf('failed verification check for filter "%s"', $this->filter_expression_text),
                    Logger::INFO,
                    [
                        $raw_info,
                    ]
                )
                ;
            }

            if ($e) {
                if ($logger) {
                    $this->getLogger()->log(
                        sprintf('filter error: "%s"', $e->getMessage()),
                        Logger::INFO,
                        [
                            $this->filter_expression_text,
                        ]
                    )
                    ;
                }
            }
        }
    }

    /**
     * @return ExpressionLanguage
     */
    private function _getExpressionLang()
    {
        if (!$this->_exp_lang) {
            $this->_exp_lang = new ExpressionLanguage(new ArrayParserCache());
        }

        return $this->_exp_lang;
    }

    protected function getFilterExpression()
    {
        return $this->filter_expression_text;
    }

    public function setFilterExpression($filter_expression_text)
    {
        $this->filter_expression_text = $filter_expression_text;
    }

    /**
     * @return Result
     */
    abstract public function doAuthenticate();
}
