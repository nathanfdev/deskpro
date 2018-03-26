<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace DeskPRO\Bundle\ReportBundle\Dpql2\Func;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlException;
use DeskPRO\Bundle\ReportBundle\Dpql2\SqlSelect;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\Part\Prepared;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\AbstractValueRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\Renderer\Html\HtmlValueRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Links to the specified content if possible (based on output type).
 */
class DpqlLink extends AbstractDpqlFunc
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(array $arguments, SelectPart $statement, $section, array $stack, SqlSelect $select, ResultMetadata $metadata)
    {
        if (count($arguments) < 2) {
            throw new DpqlException('DPQL_LINK() requires at least 2 arguments.');
        }

        $print         = array_shift($arguments);
        $format        = array_shift($arguments);
        $formatLiteral = $this->_toLiteral($format);

        $argNames  = [];
        $argSelect = [];
        foreach ($arguments as $argument) {
            $prepped     = $argument->prepare($statement, $section, $stack, $select, $metadata);
            $argNames[]  = $prepped->name();
            $argSelect[] = $select->addSelectField($prepped->printed());
        }

        $preppedPrint = $print->prepare($statement, $section, $stack, $select, $metadata);

        $renderer = function (AbstractValueRenderer $valueRenderer, $value, array $row, AbstractRenderer $renderer, ResultMetadata $metadata) use ($formatLiteral, $argSelect) {
            return $this->formatLink($value, $formatLiteral, $argSelect, $row, $valueRenderer, $renderer, $metadata);
        };

        return new Prepared($preppedPrint->sql(), $preppedPrint->name(), false, $renderer);
    }

    /**
     * @param string                $print
     * @param string                $format
     * @param array                 $argSelect
     * @param array                 $row
     * @param AbstractValueRenderer $valueRenderer
     * @param AbstractRenderer      $renderer
     * @param ResultMetadata        $metadata
     *
     * @return string
     */
    public function formatLink($print, $format, array $argSelect, array $row, AbstractValueRenderer $valueRenderer, AbstractRenderer $renderer, ResultMetadata $metadata)
    {
        $breakEarly = $print === null || !($valueRenderer instanceof HtmlValueRenderer);
        $print      = $metadata->getGroupYColumns() === 1 && array_key_exists('hierarchy_title', $row)
               ? $row['hierarchy_title']
               : $valueRenderer->renderValue($print, 'string', $metadata);

        if ($breakEarly) {
            return $print;
        }

        $id = 0;
        foreach ($argSelect as $key) {
            $id = urlencode($renderer->getColumnValue($row, $key));
        }

        switch ($format) {
            case 'ticket':
                $link = $this->urlGenerator->generate('go_to_ticket_id', ['id' => $id]);
                break;
            case 'person':
                $link = $this->urlGenerator->generate('go_to_person_id', ['id' => $id]);
                break;
            case 'organization':
                $link = $this->urlGenerator->generate('go_to_organization_id', ['id' => $id]);
                break;
            default:
                $link = '';
        }

        return '<a href="'.htmlspecialchars($link).'" target="_blank">'.$print.'</a>';
    }
}
