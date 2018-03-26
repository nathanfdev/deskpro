<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Visitor;

use DeskPRO\Bundle\AppBundle\TermEngine\CompositeTermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\VisitorInterface;
use Psr\Log\LoggerInterface;

/**
 * Dumps some info about a term into the logger.
 */
class LoggingVisitor implements VisitorInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function visit(TermInterface $term)
    {
        if ($term instanceof CompositeTermInterface) {
            $this->logger->info('entering '.get_class($term));
            $this->logger->info(json_encode($term->getOptions()));

            foreach ($term->getTerms() as $child_term) {
                $this->visit($child_term);
            }

            $this->logger->info('leaving '.get_class($term));
        }

        $this->logger->info('processing '.get_class($term));
        $this->logger->info(json_encode($term->getOptions()));
    }
}
