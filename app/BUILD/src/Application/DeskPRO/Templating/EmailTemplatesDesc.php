<?php

/**
 * DeskPRO.
 *
 * @deprecated use DeskPRO\Bundle\AppBundle\Templating\EmailTemplatesDesc instead;
 *
 * @category Templating
 */

namespace Application\DeskPRO\Templating;

use Application\DeskPRO\Translate\Translate;

/**
 * Class EmailTemplatesDesc.
 */
class EmailTemplatesDesc
{
    /**
     * @var array
     */
    private $manifest;

    /**
     * @param null $manifest_path
     */
    public function __construct($manifest_path = null)
    {
        if ($manifest_path === null) {
            $manifest_path = DP_ROOT.'/src/Application/DeskPRO/Resources/views/config/email-tpls.php';
        }

        $this->manifest = require $manifest_path;
    }

    /**
     * @return array
     */
    public function getManifest()
    {
        return $this->manifest;
    }

    /**
     * @param Translate $tr
     *
     * @return array
     */
    public function getManifestWithDescriptions(Translate $tr)
    {
        $ret = [];

        foreach ($this->manifest as $tpl) {
            $ret[] = $this->getTplDisplayInfo($tpl, $tr);
        }

        return $ret;
    }

    /**
     * @param array     $tpl
     * @param Translate $tr
     *
     * @return string
     */
    public function getTplDisplayInfo(array $tpl, Translate $tr)
    {
        $title_id = $this->_getTplPhraseId($tpl['name']).'_title';
        $desc_id  = $this->_getTplPhraseId($tpl['name']).'_desc';

        $show_name       = $tpl['name'];
        $show_name       = str_replace('DeskPRO:', '', $show_name);
        $show_name       = str_replace(':', '/', $show_name);
        $show_name       = str_replace('.twig', '', $show_name);
        $tpl['showName'] = $show_name;

        $tpl['title'] = $tr->hasPhrase($title_id) ? $tr->phrase($title_id) : $title_id;
        $tpl['desc']  = $tr->hasPhrase($desc_id) ? $tr->phrase($desc_id) : $desc_id;

        return $tpl;
    }

    /**
     * Gets a list of tempaltes grouped by type and group, with translated titles and descriptions.
     *
     * @param Translate $tr
     *
     * @return array
     */
    public function getProcessedList(Translate $tr)
    {
        $manifest = $this->getManifestWithDescriptions($tr);

        $ret = [];

        foreach ($manifest as $tpl) {
            $type  = $tpl['typeId'];
            $group = $tpl['groupId'];

            if (!isset($ret[$type])) {
                $ret[$type] = [
                    'typeId' => $type,
                    'title'  => $tr->phrase("adm.email_templates.$type"),
                    'groups' => [],
                ];
            }
            if (!isset($ret[$type]['groups'][$group])) {
                $ret[$type]['groups'][$group] = [
                    'groupId'   => $group,
                    'title'     => $tr->phrase("adm.email_templates.{$type}_{$group}"),
                    'templates' => [],
                ];
            }

            $ret[$type]['groups'][$group]['templates'][] = $tpl;
        }

        return $ret;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function _getTplPhraseId($name)
    {
        $name = str_replace('DeskPRO:', '', $name);
        $name = str_replace('.', '-', $name);
        $name = str_replace(':', '_', $name);
        $name = str_replace('-twig', '', $name);
        $name = str_replace('-html', '', $name);

        return 'adm.email_templates.'.$name;
    }
}
