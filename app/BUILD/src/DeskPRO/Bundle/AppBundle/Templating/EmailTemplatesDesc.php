<?php

/**
 * DeskPRO.
 *
 * @category Templating
 */

namespace DeskPRO\Bundle\AppBundle\Templating;

use Application\DeskPRO\Translate\Translate;

class EmailTemplatesDesc
{
    /**
     * @var array
     */
    private $manifest;

    /**
     * @param null $manifestPath
     */
    public function __construct($manifestPath = null)
    {
        if ($manifestPath === null) {
            $manifestPath = DP_ROOT.'/src/Application/DeskPRO/Resources/views/config/email-tpls.php';
        }

        $this->manifest = require $manifestPath;
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
        $titleId = $this->_getTplPhraseId($tpl['name']).'_title';
        $descId  = $this->_getTplPhraseId($tpl['name']).'_desc';

        $showName        = $tpl['name'];
        $showName        = str_replace('DeskPRO:', '', $showName);
        $showName        = str_replace(':', '/', $showName);
        $showName        = str_replace('.twig', '', $showName);
        $tpl['showName'] = $showName;

        $tpl['title'] = $tr->hasPhrase($titleId) ? $tr->phrase($titleId) : $titleId;
        $tpl['desc']  = $tr->hasPhrase($descId) ? $tr->phrase($descId) : $descId;

        return $tpl;
    }

    /**
     * Gets a list of templates grouped by type and group, with translated titles and descriptions.
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
            $type     = $tpl['typeId'];
            $group    = $tpl['groupId'];
            $subGroup = $tpl['subGroupId'];

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
                    'subGroups' => [],
                ];
            }
            if (!isset($ret[$type]['groups'][$group]['subGroups'][$subGroup])) {
                $ret[$type]['groups'][$group]['subGroups'][$subGroup] = [
                    'subGroupId' => $subGroup,
                    'title'      => $tr->phrase("adm.email_templates.{$type}_{$subGroup}"),
                    'templates'  => [],
                ];
            }

            $ret[$type]['groups'][$group]['subGroups'][$subGroup]['templates'][] = $tpl;
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
