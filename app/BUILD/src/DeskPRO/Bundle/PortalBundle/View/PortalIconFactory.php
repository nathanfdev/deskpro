<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\View;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleAttachment;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackAttachment;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsAttachment;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Orb\Util\Strings;

class PortalIconFactory
{
    protected static $colors = [
        'file-pdf'   => '#e74c3c', // red
        'file-excel' => '#3bb870', // green
        'file-word'  => '#4681b2', // blue
    ];

    protected static $fa_icons = [
        'zip'     => 'file-archive',
        'gz'      => 'file-archive',
        'tar'     => 'file-archive',
        'bz2'     => 'file-archive',
        's7z'     => 'file-archive',
        '7z'      => 'file-archive',
        'ace'     => 'file-archive',
        'pdf'     => 'file-pdf',
        'gif'     => 'file-image',
        'bmp'     => 'file-image',
        'jpg'     => 'file-image',
        'jpeg'    => 'file-image',
        'png'     => 'file-image',
        'txt'     => 'file-alt',
        'xls'     => 'file-excel',
        'xlsx'    => 'file-excel',
        'xlt'     => 'file-excel',
        'xltx'    => 'file-excel',
        'xltm'    => 'file-excel',
        'xlm'     => 'file-excel',
        'xlsm'    => 'file-excel',
        'numbers' => 'file-excel',
        'pptx'    => 'file-powerpoint',
        'ppt'     => 'file-powerpoint',
        'keynote' => 'file-powerpoint',
        'doc'     => 'file-word',
        'docx'    => 'file-word',
        'dot'     => 'file-word',
        'dotx'    => 'file-word',
        'pages'   => 'file-word',
        'mp4'     => 'file-video',
        'avi'     => 'file-video',
        'wmv'     => 'file-video',
        'mpg'     => 'file-video',
        'mp3'     => 'file-audio',
        'wav'     => 'file-audio',
        'wma'     => 'file-audio',
    ];

    /**
     * @var BrandStack
     */
    private $brand_stack;

    public function __construct(BrandStack $brand_stack)
    {
        $this->brand_stack = $brand_stack;
    }

    /**
     * Will return HTML representing an icon for any content type (dl, blob, article, news, feedback).
     *
     * @param $content
     *
     * @return string
     */
    public function makeContentIcon($content)
    {
        if ($content instanceof Download) {
            return $this->makeFileIcon($content);
        } elseif ($content instanceof Blob) {
            return $this->makeFileIcon($content);
        } elseif ($content instanceof TicketAttachment) {
            return $this->makeFileIcon($content->getBlob());
        } elseif ($content instanceof FeedbackAttachment) {
            return $this->makeFileIcon($content->getBlob());
        } elseif ($content instanceof Article) {
            return $this->makeArticleIcon($content);
        } elseif ($content instanceof ArticleAttachment) {
            return $this->makeFileIcon($content->getBlob());
        } elseif ($content instanceof News) {
            return $this->makeNewsIcon($content);
        } elseif ($content instanceof NewsAttachment) {
            return $this->makeFileIcon($content->getBlob());
        } elseif ($content instanceof Feedback) {
            return $this->makeFeedbackIcon($content);
        } elseif ($content instanceof Topic) {
            return $this->makeTopicIcon($content);
        } elseif ($content instanceof Ticket) {
            return $this->makeTicketIcon($content);
        }

        throw new \InvalidArgumentException('PortalIconFactory::makeContentIcon requires a content entity');
    }

    /**
     * Will return HTML representing an icon for any download entity or blob entity.
     *
     * @param $blob
     *
     * @return string
     */
    public function makeFileIcon($blob)
    {
        // allow download entities to be passed directly
        if ($blob instanceof Download) {
            $extension = Strings::getExtension($blob->getFileName());
        } else {
            if (!$blob instanceof Blob) {
                throw new \InvalidArgumentException('can only make a file icon for a blob');
            }

            $extension = $blob->getExtension();
        }

        if ($fa = $this->getFontAwesomeCssClassForFileExtension($extension)) {
            $style_bit = '';
            if ($color = $this->getColor($fa)) {
                $style_bit = ' style="color: '.$color.'"';
            }

            return '<i class="far fa-'.$fa.'"'.$style_bit.'></i>';
        }

        return '<i class="far fa-file"></i>';
    }

    /**
     * Will return HTML representing an icon for any article.
     *
     * @param Article $article
     *
     * @return string
     */
    public function makeArticleIcon(Article $article)
    {
        return '<i class="far fa-file-alt"></i>';
    }

    /**
     * Will return HTML representing an icon for any news.
     *
     * @param News $news
     *
     * @return string
     */
    public function makeNewsIcon(News $news)
    {
        return '<i class="far fa-file-alt"></i>';
    }

    /**
     * Will return HTML representing an icon for any feedback.
     *
     * @param Feedback $feedback
     *
     * @return string
     */
    public function makeFeedbackIcon(Feedback $feedback)
    {
        return '<i class="far fa-file-alt"></i>';
    }

    /**
     * Will return HTML representing an icon for any topic.
     *
     * @param Topic $topic
     *
     * @return string
     */
    public function makeTopicIcon(Topic $topic)
    {
        return '<i class="fas fa-book"></i>';
    }

    /**
     * Will return HTML representing an icon for any ticket.
     *
     * @param Ticket $ticket
     *
     * @return string
     */
    public function makeTicketIcon(Ticket $ticket)
    {
        return '<i class="far fa-life-ring"></i>';
    }

    /**
     * what font awesome icon (if any) should we use for this file ext.
     *
     * @param $file_extension
     *
     * @return string|null
     */
    public function getFontAwesomeCssClassForFileExtension($file_extension)
    {
        if (array_key_exists($file_extension, self::$fa_icons)) {
            return self::$fa_icons[$file_extension];
        }

        return;
    }

    /**
     * depending on brand settings, what color should we use (if any) for this ext icon.
     *
     * @param $css_class_or_ext
     *
     * @return string|null
     */
    public function getColor($css_class_or_ext)
    {
        // portal has a setting for colors
        if (!$this->brand_stack->getActive()->getSetting('portal.use_icon_colors', false)) {
            return;
        }

        if (array_key_exists($css_class_or_ext, self::$colors)) {
            return self::$colors[$css_class_or_ext];
        }

        return;
    }
}
