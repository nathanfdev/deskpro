<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\EventListener;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalEngineEvent;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalEngineEvents;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpressionLanguage;
use Orb\Util\Arrays;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class DbalQueryManipulatorListener.
 */
class DbalQueryManipulatorListener implements EventSubscriberInterface
{
    /**
     * @var TermEngineExpressionLanguage
     */
    private $expression_language;

    /**
     * Constructor.
     *
     * @param TermEngineExpressionLanguage $expression_language
     */
    public function __construct(TermEngineExpressionLanguage $expression_language)
    {
        $this->expression_language = $expression_language;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            DbalEngineEvents::MANIPULATE_QUERY => 'onManipulateQuery',
        ];
    }

    /**
     * When this event is fired from the DbalEngine, ensure agent
     * permissions are set on the query, and resolve query parameters.
     *
     * @param DbalEngineEvent $event
     */
    public function onManipulateQuery(DbalEngineEvent $event)
    {
        $query   = $event->getQuery();
        $context = $event->getContext();

        $this->ensureAgentPermissions($query, $context);
        $this->resolveParameters($query, $context);
    }

    /**
     * @param DbalQuery         $query
     * @param TermEngineContext $context
     */
    public function ensureAgentPermissions(DbalQuery $query, TermEngineContext $context)
    {
    }

    /**
     * @param DbalQuery         $query
     * @param TermEngineContext $context
     */
    public function resolveParameters(DbalQuery $query, TermEngineContext $context)
    {
        foreach ($query->getParameters() as $key => $val) {
            $resolved = $this->resolveParam($val, $context);
            if (is_array($resolved)) {
                $resolved = Arrays::flatten($resolved); // always flatten arrays
            }
            if ($resolved !== $val) {
                $query->replaceParameter($key, $resolved);
            }
        }
    }

    /**
     * @param mixed             $val
     * @param TermEngineContext $context
     *
     * @return array|string
     */
    private function resolveParam($val, TermEngineContext $context)
    {
        if (is_array($val)) {
            $new_val = [];

            foreach ($val as $key => $value) {
                $resolved_inside_array = $this->resolveParam($value, $context);
                $new_val[$key]         = $resolved_inside_array;
            }

            return $new_val;
        }

        if ($val instanceof TermEngineExpression) {
            return $this->evalExpression($val, $context);
        }

        return $val;
    }

    /**
     * @param TermEngineExpression $val
     * @param TermEngineContext    $context
     *
     * @return string
     */
    private function evalExpression(TermEngineExpression $val, TermEngineContext $context)
    {
        return $this->expression_language->evaluate((string) $val, [
            'agent' => $context->getAgent(),
        ]);
    }
}
