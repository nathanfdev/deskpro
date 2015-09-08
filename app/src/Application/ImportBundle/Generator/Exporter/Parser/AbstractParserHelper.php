<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Exporter\Parser;

use Application\ImportBundle\Generator\AbstractGenerator;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerException;

/**
 * Class AbstractParserHelper
 * @package Application\ImportBundle\Generator\Exporter\Parser
 */
abstract class AbstractParserHelper extends AbstractGenerator implements ParserHelperInterface
{
    /**
     * Log skipping exception
     *
     * @param string            $prefix
     * @param string            $entity_type
     * @param string            $ref_column
     * @param SkippingException $e
     */
    protected function logSkippingException($prefix, $entity_type, $ref_column, SkippingException $e)
    {
        $data = $e->getData();
        $oid  = isset($data[$ref_column]) ? $data[$ref_column] : '?';

        $this->logWarning(sprintf('[%s #%s] Skipping exception with %s: %s', $prefix, $oid, $entity_type, $e->getMessage()));
        $this->logWarning(json_encode($data));
    }

    /**
     * Log transformer exception
     *
     * @param string               $prefix
     * @param string               $entity_type
     * @param string               $ref_column
     * @param TransformerException $e
     */
    protected function logTransformerException($prefix, $entity_type, $ref_column, TransformerException $e)
    {
        $data = $e->getEntity();
        $oid  = isset($data[$ref_column]) ? $data[$ref_column] : '?';

        $this->logWarning(sprintf(
            '[%s #%s] Unable to transform `%s`.`%s` property to `%s` (Skipping): %s',
            $prefix, $oid, $entity_type, $e->getProperty(), $e->getTransformerType(), $e->getMessage()
        ));
        $this->logWarning(json_encode($data));
    }

    /**
     * Log unknown exception
     *
     * @param string     $prefix
     * @param string     $entity_type
     * @param string     $ref_column
     * @param \Exception $e
     * @param array      $data
     */
    protected function logUnknownException($prefix, $entity_type, $ref_column, \Exception $e, array $data)
    {
        $oid = isset($data[$ref_column]) ? $data[$ref_column] : '?';

        $this->logError(sprintf(
            '[%s #%s] Invalid %s record found (Skipping): Unknown error: %s',
            $prefix, $oid, $entity_type, $e->getMessage()
        ));
        $this->logError(json_encode($data));
    }
}
