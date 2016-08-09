<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\DeskPRO\NewSearch\Provider;

use Elastica\Exception\Bulk\ResponseException as BulkResponseException;
use FOS\ElasticaBundle\Doctrine\ORM\Provider;

/**
 * DeskPRO Doctrine Provider.
 *
 * Extends the Doctrine provider to provide a helper method for counting
 * and enables limiting the population process with the help of a new
 * option "limit".
 *
 * Also adds better garbage collection procedure.
 */
class Doctrine extends Provider
{
    protected function doPopulate($options, \Closure $loggerClosure = null)
    {
        $manager = $this->managerRegistry->getManagerForClass($this->objectClass);

        $queryBuilder = $this->createQueryBuilder($options['query_builder_method']);
        $nbObjects    = $this->countObjects($queryBuilder);
        $offset       = $options['offset'];

        for (; $offset < $nbObjects; $offset += $options['batch_size']) {
            $sliceSize = $options['batch_size'];
            try {
                $objects   = $this->fetchSlice($queryBuilder, $options['batch_size'], $offset);
                $sliceSize = count($objects);
                $objects   = $this->filterObjects($options, $objects);

                if (!empty($objects)) {
                    $this->objectPersister->insertMany($objects);
                }
            } catch (BulkResponseException $e) {
                if (!$options['ignore_errors']) {
                    throw $e;
                }

                if (null !== $loggerClosure) {
                    $loggerClosure(
                        $options['batch_size'],
                        $nbObjects,
                        sprintf('<error>%s</error>', $e->getMessage())
                    );
                }
            }

            if ($options['clear_object_manager']) {
                $manager->clear();
            }

            usleep($options['sleep']);

            if (null !== $loggerClosure) {
                $loggerClosure($sliceSize, $nbObjects);
            }

            if ($options['single_batch']) {
                break;
            }
        }
    }

    protected function configureOptions()
    {
        parent::configureOptions();

        $this->resolver->setDefaults(array(
            'single_batch' => false,
        ));
    }

    public function getCounts()
    {
        return $this->countObjects($this->createQueryBuilder('createSearchQueryBuilder'));
    }
}
