<?php

namespace DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookVars;

use DeskPRO\Bundle\AppBundle\Webhooks\ScriptEvaluator;
use DeskPRO\Bundle\AppBundle\Webhooks\WebhookInvocation;

class SearchTermVars
{
    /**
     * @param array                   $term
     * @param array|ScriptEvaluator[] $evaluators
     *
     * @return bool
     */
    public static function hasVars(array $term, array $evaluators)
    {
        $options = $term['options'];
        $search  = is_array($options) ? $options : [$options];

        $hasTermVars = false;
        $iterator    = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($search));
        while ($iterator->valid() && !$hasTermVars) {
            $value = $iterator->current();
            $iterator->next();
            for ($evaluator = reset($evaluators); !$hasTermVars && !empty($evaluator); $evaluator = next($evaluators)) {
                $hasTermVars = $evaluator->canEvaluate($value);
            }
        }

        return $hasTermVars;
    }

    /**
     * @param array                   $term
     * @param array|ScriptEvaluator[] $evaluators
     * @param WebhookInvocation       $invocation
     *
     * @return array
     */
    public static function evaluate(array $term, array $evaluators, WebhookInvocation $invocation)
    {
        $options          = $term['options'];
        $evaluatedOptions = is_array($options) ? $options : [$options];
        $result           = self::evaluateOptions($evaluatedOptions, $evaluators, $invocation);

        $nextOptions     = is_array($options) ? $result : array_pop($result);
        $term['options'] = $nextOptions;

        return $term;
    }

    /**
     * @param array                   $array
     * @param array|ScriptEvaluator[] $evaluators
     * @param WebhookInvocation       $invocation
     *
     * @return array
     */
    private static function evaluateOptions(array $array, array $evaluators, WebhookInvocation $invocation)
    {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $array[$k] = self::evaluateOptions($v, $evaluators, $invocation);
            } else {
                $valueEvaluators = [];
                foreach ($evaluators as $evaluator) {
                    if ($evaluator->canEvaluate($v)) {
                        $valueEvaluators[] = $evaluator;
                    }
                }

                /** @var ScriptEvaluator $evaluator */
                $evaluator = null;
                if (1 === count($valueEvaluators)) {
                    $evaluator = array_pop($valueEvaluators);
                }

                if ($evaluator) {
                    $array[$k] = $evaluator->evaluate($invocation, $v);
                }
            }
        }

        return $array;
    }
}
