<?php

namespace DeskPRO\Bundle\AppBundle\Renderer;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\CategoryAbstract;
use Application\DeskPRO\Entity\CommunityTopicStatusCategory;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Translate\HasPhraseName;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Entity\HasIconProperty;
use DeskPRO\Bundle\AppBundle\Entity\IconProperty;
use Exception;

class PortalIconRenderer
{
    public function getIconHtml(IconProperty $icon, $object = null, $asDownloadUrl = false, $isRounded = false)
    {
        if ($icon->getUrnNs() === IconProperty::$blobNs) {
            if ($icon->getBlob()) {
                return '<img src="'.$icon->getBlob()->{$asDownloadUrl ? 'getDownloadUrl' : 'getFileUrl'}().'" alt="icon" class="'.($isRounded ? 'rounded-circle' : '').'" />';
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

        throw new Exception(sprintf('Missing urn on icon %d', $icon->getId()));
    }

    public function getIconHtmlFrom(HasIconProperty $object, $options = [])
    {
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
        $class = 'far file';
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

        return '<i class="dp-po-icon '.$class.'" style="color:'.$this->getIconColor($object).'"></i>';
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
        static $definedColors = ['#a64e40', '#bc7062', '#cf9387', '#b1f3fb', '#93eefa', '#6deaf8', '#4fa240', '#76b566', '#99c78b', '#ffb7c3', '#ff9eb0', '#fc849d', '#f17444', '#f99168', '#ffad8c', '#b0a4fd', '#9087fc', '#6a6bfa', '#a7eba1', '#b9efb3', '#cbf3c6', '#ffbcca', '#fea5b9', '#fb8da8', '#eae3c9', '#efe9d3', '#f3eede', '#ecddfb', '#e5d2fa', '#dec7f8', '#a3ebab', '#b6efbc', '#c9f3cd', '#ffb4be', '#ff9baa', '#fc8096', '#71e8ea', '#94edee', '#b2f2f2', '#b5d5fc', '#9ac7fa', '#7bbaf9', '#e27ceb', '#ea98ef', '#f1b2f3', '#d1ccfc', '#c1bcfa', '#b0acf9', '#f9c8b6', '#a6f0a9'];

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
