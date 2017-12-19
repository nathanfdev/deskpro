<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Dpql2\Renderer;

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
