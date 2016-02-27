<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
