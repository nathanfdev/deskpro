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

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContextStorage;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractValueRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;
use Doctrine\DBAL\Connection;

/**
 * Handler for DPQL_OBJ_LANG function.
 *
 * DPQL_OBJ_LANG(field, typename, prop = "title")
 *
 * Example: DPQL_OBJ_LANG(ticket_object_use_logs.snippet.category.id, 'text_snippet_categories')
 * Example: DPQL_OBJ_LANG(ticket_object_use_logs.snippet.id, 'text_snippet')
 */
class DpqlObjLang extends AbstractDpqlFunc
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DpqlContextStorage
     */
    private $contextStorage;

    /**
     * Constructor.
     *
     * @param Connection         $connection
     * @param DpqlContextStorage $contextStorage
     */
    public function __construct(Connection $connection, DpqlContextStorage $contextStorage)
    {
        $this->connection     = $connection;
        $this->contextStorage = $contextStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        $expression = reset($arguments);
        $prepped    = $expression->prepare($statement, $section, $stack, $select, $metadata);

        if (count($arguments) < 2) {
            throw new DpqlException('DPQL_OBJ_LANG() Must have at least a field (arg1) and a type (arg2).');
        }

        $sql = $prepped->sql();

        $context = $this->contextStorage->getContext();
        $person  = $context ? $context->getPerson() : null;
        $refType = $this->_toLiteral($arguments[1]);
        $refProp = !empty($arguments[2]) ? $this->_toLiteral($arguments[2]) : 'title';
        $langId  = $person instanceof Person ? $person->getLanguage() : 0;

        $renderer = function (AbstractValueRenderer $valueRenderer, $value, array $row, AbstractRenderer $renderer, ResultMetadata $metadata) use ($refType, $refProp, $langId) {
            if ($value === null) {
                return $valueRenderer->renderValue(null, 'string', $metadata);
            }

            $value = $valueRenderer->renderValue($value, 'id', $metadata);
            $text  = $this->connection->fetchColumn('
                SELECT value
                FROM object_lang
                WHERE ref = ? AND prop_name = ? AND language_id = ?
            ', [$refType.".$value", $refProp, $langId]);

            if (!$text) {
                $text = $this->connection->fetchColumn('
                    SELECT value
                    FROM object_lang
                    WHERE ref = ? AND prop_name = ? AND language_id != ? AND value != ""
                    LIMIT 1
                ', [$refType.".$value", $refProp, $langId]);
            }

            if (!$text) {
                $text = "[$refType] $value";
            }

            return $text;
        };

        return new Prepared($sql, 'DPQL_OBJ_LANG('.$prepped->name().')', false, $renderer);
    }
}
