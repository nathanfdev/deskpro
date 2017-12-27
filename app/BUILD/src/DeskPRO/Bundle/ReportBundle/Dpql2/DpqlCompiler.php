<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ReportBundle\Dpql2;

use Application\DeskPRO\Entity\ReportWidget;
use Application\LegacyApiBundle\Service\DashboardWidget;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use Doctrine\ORM\EntityManager;

/**
 * Compiles a DPQL string statement into a statement object.
 */
class DpqlCompiler
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Lexer
     */
    protected $lexer;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param Lexer         $lexer
     * @param Parser        $parser
     */
    public function __construct(EntityManager $em, Lexer $lexer, Parser $parser)
    {
        $this->em     = $em;
        $this->lexer  = $lexer;
        $this->parser = $parser;
    }

    /**
     * Compiles the given DPQL string to a statement object.
     *
     * @param string $input
     * @param array  $placeholders
     *
     * @return SelectPart
     */
    public function compile($input, array $placeholders = [])
    {
        $input = preg_replace('/DISPLAY [^\n]+\n/', '', $input);
        $input = $this->replacePlaceholders($input, $placeholders);
        $input = $this->replaceVariables($input, $placeholders);

        $statement = $this->lexAndParse($input);
        $statement->prepare();

        return $statement;
    }

    /**
     * Lexes and parses a DPQL string. Only ensures that it's syntactically valid.
     *
     * @param string $input
     *
     * @return SelectPart
     */
    public function lexAndParse($input)
    {
        $this->lexer->setInput($input);

        while ($this->lexer->yylex()) {
            $this->parser->line = $this->lexer->line;
            $this->parser->doParse($this->lexer->token, $this->lexer->value);
        }
        $this->parser->doParse(0, 0);

        return $this->parser->getResult();
    }

    /**
     * @param       $input
     * @param array $placeholders
     *
     * @return mixed
     */
    public function replacePlaceholders($input, array $placeholders = [])
    {
        $repository = $this->em->getRepository(ReportWidget::class);

        $groupParams = $repository->getReportGroupParams();

        $input = preg_replace_callback(
            '/%(\d+):DATE_GROUP%/',
            function ($match) use ($placeholders, $groupParams) {
                if (isset($placeholders[$match[1]])) {
                    $value = strval($placeholders[$match[1]]);
                    if (isset($groupParams['dates'][$value])) {
                        return $groupParams['dates'][$value][1];
                    }
                }

                $first = reset($groupParams['dates']);

                return $first[1];
            },
            $input
        );

        $input = preg_replace_callback(
            '/%(\d+):FIELD_GROUP:([^:%]+)(:([^%]+))?%/',
            function ($match) use ($placeholders, $groupParams) {
                $type = $match[2];
                $table = isset($match[4]) ? $match[4] : $type;

                if (isset($placeholders[$match[1]])) {
                    $value = strval($placeholders[$match[1]]);
                    if (isset($groupParams['fields'][$type][$value])) {
                        return sprintf($groupParams['fields'][$type][$value][1], $table);
                    }
                }

                if (isset($groupParams['fields'][$type])) {
                    $first = reset($groupParams['fields'][$type]);

                    return sprintf($first[1], $table);
                }

                return 'NULL';
            },
            $input
        );

        $input = preg_replace_callback(
            '/%(\d+):STATUS_GROUP:([^:%]+)(:([^%]+))?%/',
            function ($match) use ($placeholders, $groupParams) {
                $type = $match[2];
                $table = isset($match[4]) ? $match[4] : $type;

                if (isset($placeholders[$match[1]])) {
                    $value = strval($placeholders[$match[1]]);
                    if (isset($groupParams['statuses'][$type][$value])) {
                        return sprintf($groupParams['statuses'][$type][$value][1], $table);
                    }
                }

                if (isset($groupParams['statuses'][$type])) {
                    $first = reset($groupParams['statuses'][$type]);

                    return sprintf($first[1], $table);
                }

                return '1';
            },
            $input
        );

        $input = preg_replace_callback(
            '/%(\d+):ORDER_GROUP:([^:%]+)(:([^%]+))?%/',
            function ($match) use ($placeholders, $groupParams) {
                $type = $match[2];
                $table = isset($match[4]) ? $match[4] : $type;

                if (isset($placeholders[$match[1]])) {
                    $value = strval($placeholders[$match[1]]);
                    if (isset($groupParams['orders'][$type][$value])) {
                        return sprintf($groupParams['orders'][$type][$value][1], $table);
                    }
                }

                if (isset($groupParams['orders'][$type])) {
                    $first = reset($groupParams['orders'][$type]);

                    return sprintf($first[1], $table);
                }

                return 'NULL';
            },
            $input
        );

        return $input;
    }

    /**
     * @param       $input
     * @param array $placeholders
     *
     * @return mixed
     */
    public function replaceVariables($input, $placeholders = [])
    {
        if (!isset($placeholders['variables'])) {
            return $input;
        }
        $variables = [];
        foreach ($placeholders['variables'] as $var) {
            $variables[$var['name']] = $var;
        }

        $repository  = $this->em->getRepository(ReportWidget::class);
        $groupParams = $repository->getReportGroupParams();
        $that        = $this;

        $input = preg_replace_callback(
            '#(\$\{([a-zA-Z0-9_]+)\})#',
            function ($match) use ($input, $variables, $placeholders, $groupParams, $that) {
                $varName = $match[2];
                if (isset($variables[$varName])) {
                    $variable = $variables[$varName];
                    switch ($variable['type']) {
                        case 'dates':
                            return $that->replaceDate($variable, $varName, $variables);
                        case 'fields':
                        case 'orders':
                        case 'statuses':
                            return $that->replaceGroup($variable, $variables, $variable['type']);
                    }
                }

                return $match[0];
            },
            $input
        );

        return $input;
    }

    /**
     * @param $var
     * @param $varName
     * @param $variables
     *
     * @return mixed
     */
    protected function replaceDate($var, $varName, $variables)
    {
        $repository  = $this->em->getRepository(ReportWidget::class);
        $groupParams = $repository->getReportGroupParams();

        $default = isset($var['default']) ? $var['default'] : null;

        if (isset($variables[$varName])) {
            $valueExists = isset($variables[$varName]['value']) && $variables[$varName]['value'];
            $value       = $valueExists ? strval($variables[$varName]['value']) : $default;
            if ($value != DashboardWidget::WIDGET_VALUE_FROM_REPORT && isset($groupParams['dates'][$value])) {
                return $groupParams['dates'][$value][1];
            }
        }

        $first = reset($groupParams['dates']);

        return $first[1];
    }

    /**
     * @param $var
     * @param $variables
     * @param $groupType
     *
     * @return string
     */
    protected function replaceGroup($var, $variables, $groupType)
    {
        $repository  = $this->em->getRepository(ReportWidget::class);
        $groupParams = $repository->getReportGroupParams();

        $varName = $var['name'];
        $type    = $var['field_type'];
        $table   = isset($var['table']) && $var['table'] ? $var['table'] : $var['field_type'];
        $default = isset($var['default']) ? $var['default'] : null;

        if (isset($variables[$varName])) {
            $valueExists = isset($variables[$varName]['value']) && $variables[$varName]['value'];
            $value       = $valueExists ? strval($variables[$varName]['value']) : $default;
            if (isset($groupParams[$groupType][$type][$value])) {
                return sprintf($groupParams[$groupType][$type][$value][1], $table);
            }
        }

        if ($default) {
            return $default;
        }

        if (isset($groupParams[$groupType][$type])) {
            $first = reset($groupParams[$groupType][$type]);

            return sprintf($first[1], $table);
        }

        return 'NULL';
    }
}
