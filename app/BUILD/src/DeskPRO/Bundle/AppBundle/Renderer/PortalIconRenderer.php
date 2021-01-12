<?php

namespace DeskPRO\Bundle\AppBundle\Renderer;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\CategoryAbstract;
use Application\DeskPRO\Entity\CommunityTopicStatusCategory;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Translate\HasPhraseName;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Entity\HasIconProperty;
use DeskPRO\Bundle\AppBundle\Entity\IconProperty;
use Exception;

class PortalIconRenderer
{
    private $twig;

    public function __construct($twig)
    {
        $this->twig = $twig;
    }

    public function getIconHtml(IconProperty $icon, $object = null, $asDownloadUrl = false, $isRounded = false)
    {
        if ($icon->getUrnNs() === IconProperty::$blobNs) {
            if ($icon->getBlob()) {
                return '<span class="dp-po-icon"><img src="'.$icon->getBlob()->getDownloadUrl().'" alt="icon" class="'.($isRounded ? 'rounded-circle' : '').'" /></span>';
            }
        }
        if ($icon->getUrnNs() === IconProperty::$faNs) {
            $iconOptions = $icon->getOptions();
            $iconStyle   = 'fas';
            if (isset($iconOptions['style'])) {
                $iconStyle = $iconOptions['style'];
            }
            $style = '';
            if ($object) {
                $style = ' style="color:'.$this->getIconColor($object).'"';
            }

            return '<i class="dp-po-icon '.$iconStyle.' '.$icon->getUrnPath().'"'.$style.'></i>';
        }
        if ($icon->getUrnNs() === IconProperty::$emoji) {
            return urldecode($icon->getUrnPath());
        }

        throw new Exception(sprintf('Missing urn on icon %d: %s', $icon->getId(), $icon->getUrnNs()));
    }

    public function getIconHtmlFrom(HasIconProperty $object, $options = [])
    {
        if ($object instanceof Guide) {
            $style = ' style="background-color: var(--warning)"';
            if ($object->getColor()) {
                $style = ' style="background-color:'.$object->getColor().'"';
            }
            $figureClass = '';
            if (isset($options['figure_class'])) {
                $figureClass = $options['figure_class'];
            }
            if ($object->getIcon()) {
                $icon = $this->getIconHtml(
                    $object->getIcon(),
                    $object,
                    isset($options['as_download_url']) && $options['as_download_url'],
                    isset($options['is_rounded']) && $options['is_rounded']
                );
                if ($icon) {
                    return '<figure class="dp-po-icon '.$figureClass.'" '.$style.' >'.$icon.'</figure>';
                }
            }

            return '<figure class="dp-po-icon '.$figureClass.'" '.$style.' ><img class="dp-icon-svg" src="'.$this->twig->getExtension('asset')->getAssetUrl('img/page-icons/guide-default.svg', 'help_center').'" alt=""></figure>';
        }
        if ($object->getIcon()) {
            $icon = $this->getIconHtml(
                $object->getIcon(),
                $object,
                isset($options['as_download_url']) && $options['as_download_url'],
                isset($options['is_rounded']) && $options['is_rounded']
            );
            if ($icon) {
                return $icon;
            }
        }
        $class = 'far fa-file';
        if ($object instanceof CategoryAbstract) {
            $class = 'fas fa-folder';
        }
        if ($object instanceof Download) {
            $class = 'fas fa-download';
        }
        if ($object instanceof News) {
            $class = 'far fa-newspaper';
        }
        if ($object instanceof Article) {
            $class = 'far fa-file';
        }
        if ($object instanceof Topic) {
            $class = 'fas fa-book';
        }
        if (isset($options['class'])) {
            $class .= ' '.$options['class'];
        }

        return '<figure class="dp-po-icon"><i class="'.$class.'" style="color:'.$this->getIconColor($object).'"></i></figure>';
    }

    /**
     * @param string $id
     *
     * @return string
     */
    public function generateColor($id = null)
    {
        if (!$id) {
            $id = md5(uniqid(''));
        }

        // Generated via https://learnui.design/tools/data-color-picker.html#divergent
        // made them 25% darker
        // ['#a64e40', '#bc7062', '#cf9387', '#b1f3fb', '#93eefa', '#6deaf8', '#4fa240', '#76b566', '#99c78b', '#ffb7c3', '#ff9eb0', '#fc849d', '#f17444', '#f99168', '#ffad8c', '#b0a4fd', '#9087fc', '#6a6bfa', '#a7eba1', '#b9efb3', '#cbf3c6', '#ffbcca', '#fea5b9', '#fb8da8', '#eae3c9', '#efe9d3', '#f3eede', '#ecddfb', '#e5d2fa', '#dec7f8', '#a3ebab', '#b6efbc', '#c9f3cd', '#ffb4be', '#ff9baa', '#fc8096', '#71e8ea', '#94edee', '#b2f2f2', '#b5d5fc', '#9ac7fa', '#7bbaf9', '#e27ceb', '#ea98ef', '#f1b2f3', '#d1ccfc', '#c1bcfa', '#b0acf9', '#f9c8b6', '#a6f0a9']
        static $definedColors = ['#7d3b30', '#8d544a', '#9b6e65', '#85b6bc', '#6eb3bc', '#52b0ba', '#3b7a30', '#59884d', '#739568', '#bf8992', '#bf7784', '#bd6376', '#b55733', '#bb6d4e', '#bf8269', '#847bbe', '#6c65bd', '#5050bc', '#7db079', '#8bb386', '#98b695', '#bf8d98', '#bf7c8b', '#bc6a7e', '#b0aa97', '#b3af9e', '#b6b3a7', '#b1a6bc', '#ac9ebc', '#a795ba', '#7ab080', '#89b38d', '#97b69a', '#bf878f', '#bf7480', '#bd6071', '#55aeb0', '#6fb2b3', '#86b6b6', '#88a0bd', '#7495bc', '#5c8cbb', '#aa5db0', '#b072b3', '#b586b6', '#9d99bd', '#918dbc', '#8481bb', '#bb9689', '#7db47f'];

        static $definedColorSize;
        if (!$definedColorSize) {
            $definedColorSize = count($definedColors);
        }

        $idx = abs(crc32($id)) % $definedColorSize;

        return $definedColors[$idx];
    }

    public function getIconColor($obj)
    {
        $id = null;
        if ($obj instanceof CategoryAbstract || $obj instanceof CommunityTopicStatusCategory) {
            if ($obj->getColor()) {
                return $obj->getColor();
            }
        }
        if ($obj->getIcon()) {
            $option = $obj->getIcon()->getOptions();
            if (isset($option['color'])) {
                return $option['color'];
            }
        }
        if ($obj instanceof EntityInterface) {
            $id = $obj->getId();
        } elseif ($obj instanceof HasPhraseName) {
            $id = $obj->getPhraseName();
        } elseif (is_object($obj)) {
            $id = spl_object_hash($obj);
        } elseif (is_scalar($obj)) {
            $id = crc32($obj);
        } elseif (is_array($obj)) {
            $id = count($obj);
        }

        return $this->generateColor($id);
    }
}
