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

namespace Application\ImportBundle\Generator\Exporter\Parser;

use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;

/**
 * Abstract batch exporter parser.
 *
 * Class AbstractBatchParser
 */
abstract class AbstractBatchParser implements BatchParserInterface
{
    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * Constructor.
     *
     * @param FormatterInterface $formatter
     */
    public function __construct(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'id'            => TransformerInterface::TYPE_STRING,
            'type'          => TransformerInterface::TYPE_STRING,
            'date_created'  => TransformerInterface::TYPE_DATE,
            'date_modified' => TransformerInterface::TYPE_DATE,
            'has_remaining' => TransformerInterface::TYPE_BOOLEAN,
        ));

        $config = $this->getDefaultBatchConfig();
        $config
            ->setId($formatted['id'])
            ->setHasRemaining($formatted['has_remaining'])
            ->setDateCreated($formatted['date_created'])
            ->setDateModified($formatted['date_modified'])
        ;

        return $config;
    }
}
