<?php

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

    public function mergeResults(array $results, array $options)
    {
        // TODO: Implement mergeResults() method.
    }
}
