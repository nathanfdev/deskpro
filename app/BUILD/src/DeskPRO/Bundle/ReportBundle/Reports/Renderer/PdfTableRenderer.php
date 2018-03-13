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

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer;

use DeskPRO\Bundle\ReportBundle\Reports\Renderer\Html\HtmlTableRenderer;
use DeskPRO\Bundle\ReportBundle\Reports\Results;
use DeskPRO\Component\Pdf\PdfRendererInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Renders DPQL results to Pdf.
 */
class PdfTableRenderer implements ReportsRendererInterface
{
    /**
     * @var HtmlTableRenderer
     */
    private $htmlRenderer;

    /**
     * @var PdfRendererInterface
     */
    private $pdfRenderer;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * Constructor.
     *
     * @param HtmlTableRenderer    $htmlRenderer
     * @param PdfRendererInterface $pdfRenderer
     * @param EngineInterface      $templating
     */
    public function __construct(HtmlTableRenderer $htmlRenderer, PdfRendererInterface $pdfRenderer, EngineInterface $templating)
    {
        $this->htmlRenderer = $htmlRenderer;
        $this->pdfRenderer  = $pdfRenderer;
        $this->templating   = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public static function getOutputFormat()
    {
        return self::TYPE_TABLE;
    }

    /**
     * {@inheritdoc}
     */
    public static function getContentType()
    {
        return 'application/pdf';
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtension()
    {
        return 'pdf';
    }

    /**
     * {@inheritdoc}
     */
    public function render(Results $results, array $options = [])
    {
        $html        = $this->htmlRenderer->render($results, $options);
        $contentHtml = $this->templating->render('DeskPRO:pdf_agent:report-builder.html.twig', [
            'html'  => $html,
            'title' => isset($options['title']) ?: '',
        ]);

        return $this->pdfRenderer->render($contentHtml);
    }

    public function mergeResults(array $results)
    {
        // TODO: Implement mergeResults() method.
    }
}
