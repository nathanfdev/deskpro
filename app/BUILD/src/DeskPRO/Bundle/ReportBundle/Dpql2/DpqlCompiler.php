<?php

namespace DeskPRO\Bundle\ReportBundle\Dpql2;

use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\BillingFieldManager;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ReportWidget;
use Application\DeskPRO\EntityRepository\ReportWidget as ReportWidgetRepository;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Application\DeskPRO\Reports\ReportsWidgetService;
use DeskPRO\Bundle\ReportBundle\Dashboard\DashboardWidgetManager;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

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
     * @var SettingsResolver
     */
    protected $settingsResolver;

    /**
     * @var Lexer
     */
    protected $lexer;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var DpqlContextStorage
     */
    protected $contextStorage;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var ReportsWidgetService
     */
    protected $reportsWidgetService;

    /**
     * @var BillingFieldManager
     */
    protected $billingFieldManager;

    /**
     * Constructor.
     *
     * @param SettingsResolver     $settingsResolver
     * @param EntityManager        $em
     * @param ReportsWidgetService $reportsWidgetService
     * @param Lexer                $lexer
     * @param Parser               $parser
     * @param DpqlContextStorage   $contextStorage
     * @param TokenStorage         $tokenStorage
     */
    public function __construct(
        SettingsResolver     $settingsResolver,
        EntityManager        $em,
        ReportsWidgetService $reportsWidgetService,
        Lexer                $lexer,
        Parser               $parser,
        DpqlContextStorage   $contextStorage,
        TokenStorage         $tokenStorage
    ) {
        $this->settingsResolver     = $settingsResolver;
        $this->em                   = $em;
        $this->reportsWidgetService = $reportsWidgetService;
        $this->billingFieldManager  = App::getContainer()->getBillingFieldManager();
        $this->lexer                = $lexer;
        $this->parser               = $parser;
        $this->contextStorage       = $contextStorage;
        $this->tokenStorage         = $tokenStorage;
    }

    /**
     * Compiles the given DPQL string to a statement object.
     *
     * @param string      $input
     * @param array       $placeholders
     * @param DpqlContext $context
     *
     * @throws DpqlException
     *
     * @return SelectPart
     */
    public function compile($input, array $placeholders = [], DpqlContext $context = null)
    {
        if (strpos($input, 'LAYER WITH') !== false) {
            throw new DpqlException(
                DpqlException::getMessageByCode(DpqlException::CODE_LAYERED_DIRECT_COMPILE_ERROR),
                DpqlException::CODE_LAYERED_DIRECT_COMPILE_ERROR
            );
        }
        if (!$context) {
            $token   = $this->tokenStorage->getToken();
            $person  = $token && $token->getUser() instanceof Person ? $token->getUser() : null;
            $context = new DpqlContext($person);
        }

        $this->contextStorage->setContext($context);

        $input = preg_replace('/DISPLAY [^\n]+\n/', '', $input);
        $input = $this->replaceBillingData($input);
        $input = $this->replacePlaceholders($input, $placeholders);
        $input = $this->replaceVariables($input, $placeholders);

        try {
            $statement = $this->lexAndParse($input);
            $statement->prepare();
        } catch (DpqlException $e) {
            if ($e instanceof DpqlParseException) {
                throw $e;
            }

            throw DpqlCompileException::createFromException($e, $input);
        }

        return $statement;
    }

    /**
     * Lexes and parses a DPQL string. Only ensures that it's syntactically valid.
     *
     * @param string $input
     *
     * @return SelectPart
     */
    protected function lexAndParse($input)
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
    protected function replacePlaceholders($input, array $placeholders = [])
    {
        /** @var ReportWidgetRepository $repository */
        $repository  = $this->em->getRepository(ReportWidget::class);
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

        $settingsResolver = $this->settingsResolver;
        $input            = preg_replace_callback(
            '/%SETTING:([^:%]+)%/',
            function ($match) use ($settingsResolver) {
                $value = $settingsResolver->getGlobalSettings()->get($match[1], null);

                return $value ?: 'NULL';
            },
            $input
        );

        return $input;
    }

    public function replaceBillingData($input)
    {
        $currency = $this->settingsResolver->getGlobalSettings()->get('core_tickets.billing_currency');

        $fields     = $this->billingFieldManager->getFields();
        $selectBits = [];
        foreach ($fields as $f) {
            $selectBits[] = 'ticket_charges.custom_data['.$f->getId().'] AS \''.addslashes($f->getTitle()).'\'';
        }

        $selectBits = implode(', ', $selectBits);
        if ($selectBits) {
            $selectBits = $selectBits.', ';
        }

        $vars = [
            'variables' => [
                [
                    'name'  => 'billingCurrency',
                    'type'  => DashboardWidgetManager::WIDGET_VAR_TYPE_BILLING,
                    'value' => $currency,
                ],
                [
                    'name'  => 'billingSelectBits',
                    'type'  => DashboardWidgetManager::WIDGET_VAR_TYPE_BILLING,
                    'value' => $selectBits,
                ],
            ],
        ];

        return $this->replaceVariables($input, $vars);
    }

    /**
     * @param       $input
     * @param array $placeholders
     *
     * @return mixed
     */
    protected function replaceVariables($input, $placeholders = [])
    {
        if (!isset($placeholders['variables'])) {
            return $input;
        }
        $variables = [];
        foreach ($placeholders['variables'] as $var) {
            $variables[$var['name']] = $var;
        }

        $groupParams = $this->reportsWidgetService->getGroupParams(false);

        $input = preg_replace_callback(
            '#(\$\{\s*([a-zA-Z0-9_]+)\s*\})#',
            function ($match) use ($input, $variables, $placeholders, $groupParams) {
                $varName = $match[2];
                if (isset($variables[$varName])) {
                    $variable = $variables[$varName];
                    switch ($variable['type']) {
                        case 'dates':
                            return $this->replaceDate($variable, $varName, $variables);
                        case 'fields':
                        case 'orders':
                        case 'statuses':
                            return $this->replaceGroup($variable, $variables, $variable['type']);
                        // this would include 'value' and all custom def stuff
                        default:
                            $value = @$variable['value'] ?: @$variable['field_value'] ?: $match[0];

                            if ($variable['type'] === DashboardWidgetManager::WIDGET_VAR_TYPE_BILLING) {
                                // it's internal vars, which are not accessible by a user
                                return $value;
                            }

                            if (
                                $value === DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT
                                && $variable['type'] === 'values'
                                && isset($groupParams[$variable['type']][$variable['field_type']])) {
                                $value = array_keys($groupParams[$variable['type']][$variable['field_type']])[0];
                            }

                            return !is_numeric($value) ? $this->em->getConnection()->quote($value) : $value;
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
        $groupParams = $this->reportsWidgetService->getGroupParams(false);

        $default = isset($var['default']) ? $var['default'] : null;

        if (isset($variables[$varName])) {
            $valueExists = isset($variables[$varName]['value']) && $variables[$varName]['value'];
            $value       = $valueExists ? strval($variables[$varName]['value']) : $default;
            if ($value != DashboardWidgetManager::WIDGET_VALUE_FROM_REPORT && isset($groupParams['dates'][$value])) {
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
        $groupParams = $this->reportsWidgetService->getGroupParams(false);

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
