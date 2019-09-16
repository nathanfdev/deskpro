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
    public function getIconHtml(IconProperty $icon, $object = null)
    {
        if ($icon->getUrnNs() === IconProperty::$blobNs) {
            if ($icon->getBlob()) {
                return '<img src="'.$icon->getBlob()->getFileUrl().'" alt="icon" />';
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
            $icon = $this->getIconHtml($object->getIcon(), $object);
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

        // Generated via https://randomcolor.lllllllllllllllll.com/
        static $definedColors = ['#d1146f', '#00753c', '#e00690', '#059345', '#09318e', '#00165b', '#a3002e', '#bc2210', '#016d57', '#477f03', '#d39910', '#0a1466', '#0c8e52', '#637c07', '#0c997f', '#480d9b', '#003377', '#467a06', '#97a010', '#a8034a', '#0ea324', '#0c2b7f', '#912a0e', '#d1b60c', '#bc01bc', '#005e5c', '#11aa2a', '#4a077c', '#d615d2', '#fced19', '#560996', '#ce9402', '#05717f', '#0c9694', '#a00c27', '#24930e', '#0a2d70', '#bf09b6', '#ef17e4', '#06356b', '#bb11c1', '#127702', '#c67801', '#0a9b5c', '#23ad0d', '#f4db18', '#b70747', '#798209', '#407503', '#8c0406'];

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
