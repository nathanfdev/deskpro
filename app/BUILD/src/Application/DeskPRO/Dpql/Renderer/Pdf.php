<?php

namespace Application\DeskPRO\Dpql\Renderer;

use Application\DeskPRO\App;

/**
 * Renders DPQL results to Pdf.
 */
class Pdf extends Html
{
    /**
     * Gets the MIME content type for this type of output.
     *
     * @return string
     */
    public function getContentType()
    {
        return 'application/pdf';
    }

    /**
     * Gets the file extension for this type of output.
     *
     * @return string
     */
    public function getExtension()
    {
        return 'pdf';
    }

    /**
     * Render to the specified format and type.
     *
     * @return string
     */
    public function render()
    {
        $html = parent::render();

        $contentHtml = App::getTemplating()->render('DeskPRO:pdf_agent:report-builder.html.twig', [
            'html'  => $html,
            'title' => $this->_title,
        ]);

        $mpdf = App::$container->get('pdf_renderer');

        return $mpdf->render($contentHtml);
    }

    /**
     * Charts not supported in PDF. Returns false.
     *
     * @param string $type
     * @param array  $rows
     *
     * @return string|bool
     */
    protected function _renderChart($type, array $rows)
    {
        return false;
    }
}
