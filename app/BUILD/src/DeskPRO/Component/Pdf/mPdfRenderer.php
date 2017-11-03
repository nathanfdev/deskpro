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

namespace DeskPRO\Component\Pdf;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnv;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class mPdfRenderer.
 */
class mPdfRenderer implements PdfRendererInterface
{
    /**
     * @var mPDF
     */
    private $object;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var AppEnv
     */
    private $appEnv;

    /**
     * Constructor.
     *
     * @param BrandStack $brandStack
     * @param AppEnv     $appEnv
     */
    public function __construct(BrandStack $brandStack, AppEnv $appEnv)
    {
        $this->brandStack = $brandStack;
        $this->appEnv     = $appEnv;

        $this->object = new mPDF([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'default_font_size' => 8,
            'default_font'      => '',
            'margin_left'       => 20,
            'margin_right'      => 20,
            'margin_top'        => 40,
            'margin_bottom'     => 40,
            'margin_header'     => 10,
            'margin_footer'     => 10,
            'orientation'       => 'P',
            'tempDir'           => $appEnv->getUserTmpDir(),
            ]
        );

        $this->object->SetBasePath($this->brandStack->getActive()->getSetting('core.deskpro_url').'/');
        $this->object->shrink_tables_to_fit = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function setPageSize($size = 'A4', $orientation = 'P')
    {
        $this->object->_setPageSize($size, $orientation);
    }

    /**
     * {@inheritdoc}
     */
    public function render($contentHtml)
    {
        $this->object->WriteHTML($contentHtml);

        return $this->object->Output('', 'S');
    }

    /**
     * {@inheritdoc}
     */
    public function generateFile($contentHtml, $fileName)
    {
        $this->object->WriteHTML($contentHtml);

        return new Response($this->object->Output($fileName, 'D'), 200, ['Content-Type' => 'application/pdf']);
    }
}
