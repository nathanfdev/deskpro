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
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\Topic;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Orb\Util\Strings;

class PortalIconFactory
{
    protected static $colors = [
        'file-pdf-o'   => '#e74c3c', // red
        'file-excel-o' => '#3bb870', // green
        'file-word-o'  => '#4681b2', // blue
    ];

    protected static $fa_icons = [
        'zip'     => 'file-archive-o',
        'gz'      => 'file-archive-o',
        'tar'     => 'file-archive-o',
        'bz2'     => 'file-archive-o',
        's7z'     => 'file-archive-o',
        '7z'      => 'file-archive-o',
        'ace'     => 'file-archive-o',
        'pdf'     => 'file-pdf-o',
        'gif'     => 'file-image-o',
        'bmp'     => 'file-image-o',
        'jpg'     => 'file-image-o',
        'jpeg'    => 'file-image-o',
        'png'     => 'file-image-o',
        'txt'     => 'file-text-o',
        'xls'     => 'file-excel-o',
        'xlsx'    => 'file-excel-o',
        'xlt'     => 'file-excel-o',
        'xltx'    => 'file-excel-o',
        'xltm'    => 'file-excel-o',
        'xlm'     => 'file-excel-o',
        'xlsm'    => 'file-excel-o',
        'numbers' => 'file-excel-o',
        'pptx'    => 'file-powerpoint-o',
        'ppt'     => 'file-powerpoint-o',
        'keynote' => 'file-powerpoint-o',
        'doc'     => 'file-word-o',
        'docx'    => 'file-word-o',
        'dot'     => 'file-word-o',
        'dotx'    => 'file-word-o',
        'pages'   => 'file-word-o',
        'mp4'     => 'file-video-o',
        'avi'     => 'file-video-o',
        'wmv'     => 'file-video-o',
        'mpg'     => 'file-video-o',
        'mp3'     => 'file-audio-o',
        'wav'     => 'file-audio-o',
        'wma'     => 'file-audio-o',
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

            return '<i class="fa fa-'.$fa.'"'.$style_bit.'></i>';
        }

        return '<i class="fa fa-file-o"></i>';
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
        return '<i class="fa fa-file-text-o"></i>';
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
        return '<i class="fa fa-file-text-o"></i>';
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
        return '<i class="fa fa-file-text-o"></i>';
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
        return '<i class="fa fa-book"></i>';
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
        return '<i class="fa fa-support"></i>';
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
