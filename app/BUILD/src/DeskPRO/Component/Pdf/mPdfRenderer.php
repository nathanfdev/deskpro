<?php

namespace DeskPRO\Component\Pdf;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnv;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Component\Filesystem\TmpDir;
use DpSys\LowError\SystemErrorHandler;
use Orb\Util\Strings;
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
        global $DP_ENV;

        $this->brandStack = $brandStack;
        $this->appEnv     = $appEnv;

        $proxy = $DP_ENV->getConfig('settings.http_client.proxy');

        $this->object = new mPDF([
            'mode'                       => 'utf-8',
            'format'                     => 'A4',
            'default_font_size'          => 8,
            'default_font'               => '',
            'margin_left'                => 20,
            'margin_right'               => 20,
            'margin_top'                 => 40,
            'margin_bottom'              => 40,
            'margin_header'              => 10,
            'margin_footer'              => 10,
            'orientation'                => 'P',
            'tempDir'                    => TmpDir::makeTmpDir(),
            'curlAllowUnsafeSslRequests' => true,
            // set two below options to properly set fonts for CJK languages
            // https://mpdf.github.io/fonts-languages/choosing-a-configuration-v7-x.html#3-languagesscripts-which-require-special-fonts
            'autoScriptToLang' => true,
            'autoLangToFont'   => true,

            'curlProxy' => $proxy,
            'curlTimeout' => 3
        ]);

        $this->object->SetBasePath($this->brandStack->getActive()->getSetting('core.deskpro_url').'/');
        $this->object->shrink_tables_to_fit   = 0;
        $this->object->keep_table_proportions = false;
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
        return SystemErrorHandler::runWithoutErrorHandler(function () use ($contentHtml) {
            $this->object->WriteHTML($contentHtml);

            return $this->object->Output('', 'S');
        });
    }

    /**
     * {@inheritdoc}
     */
    public function generateFile($contentHtml, $fileName)
    {
        $fileName = Strings::getFilenameSafe($fileName);

        return SystemErrorHandler::runWithoutErrorHandler(function () use ($contentHtml, $fileName) {
            $this->object->WriteHTML($contentHtml);

            return new Response($this->object->Output($fileName, 'D'), 200, ['Content-Type' => 'application/pdf']);
        });
    }
}
