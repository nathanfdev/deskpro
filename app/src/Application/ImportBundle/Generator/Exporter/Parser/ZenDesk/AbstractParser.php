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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ParserHelperSet;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderInterface;
use DateTime;
use Exception;

/**
 * Abstract ZenDesk parser.
 *
 * Class AbstractParser
 */
abstract class AbstractParser extends \Application\ImportBundle\Generator\Exporter\Parser\AbstractParser
{
    /**
     * @var ZenDeskReaderInterface
     */
    protected $reader;

    /**
     * @var DateTime
     */
    protected $end_time;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * Constructor.
     *
     * @param ZenDeskReaderInterface $reader
     * @param FormatterInterface     $formatter
     * @param ParserHelperSet        $helpers
     */
    public function __construct(ZenDeskReaderInterface $reader, FormatterInterface $formatter, ParserHelperSet $helpers)
    {
        $this->reader    = $reader;
        $this->formatter = $formatter;
        $this->helpers   = $helpers;
    }

    /**
     * Returns batch config.
     *
     * @throws Exception
     *
     * @return BatchConfig
     */
    protected function getBatchConfig()
    {
        if ($this->config->getExporterBatchConfig()) {
            return $this->config->getExporterBatchConfig();
        }

        throw new Exception('Batch config is not defined');
    }

    /**
     * Returns a person organization name.
     *
     * @param int $id
     *
     * @return string|null
     */
    protected function getOrganizationName($id)
    {
        $organization = null;
        if ($id) {
            $organization = $this->reader->getOrganizationById($id);
            if ($organization) {
                return $organization['name'];
            }
        }

        return;
    }

    /**
     * Returns current end time.
     *
     * @return DateTime
     */
    public function getCurrentEndTime()
    {
        return $this->end_time;
    }

    /**
     * @return Helper\Attachment
     */
    protected function getAttachmentParser()
    {
        return $this->helpers->get($this, Entity\EntityInterface::TYPE_ATTACHMENT);
    }

    /**
     * @return Helper\Translations
     */
    protected function getTranslationsParser()
    {
        return $this->helpers->get($this, Entity\EntityInterface::TYPE_OBJECT_LANG);
    }
}
