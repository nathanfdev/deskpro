<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\DevBundle\Language;

use Symfony\Component\Finder\Finder;

class PhrasesFinder
{
    /**
     * @var string
     */
    private $app_root;

    /**
     * @var string[]
     */
    private $phrase_ids;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var string
     */
    private $types;

    /**
     * @var bool
     */
    private $exclude_dynamic = true;

    /**
     * PhrasesFinder constructor.
     *
     * @param string   $app_root   The root path to DeskPRO files
     * @param string[] $phrase_ids Phrase IDs to find
     * @param int      $limit      How many uses to find per phrase (e.g., 1 will be faster if you just want to
     *                             check if a phrase is used anywhere). Set 0 to have no limit.
     * @param string[] $types      Which filetypes to scan for
     */
    public function __construct($app_root, array $phrase_ids, $limit = 1, array $types = null)
    {
        $this->app_root   = $app_root;
        $this->phrase_ids = $phrase_ids;
        $this->limit      = $limit;
        $this->types      = $types;
    }

    /**
     * Include phrases we know are only used dynamic, so will not have explicit uses (i.e. always show as 'missing').
     */
    public function includeKnownDynamic()
    {
        $this->exclude_dynamic = false;
    }

    /**
     * @return \SplFileInfo[]
     */
    private function getTplList()
    {
        $iter = new \AppendIterator();

        if (in_array('twig', $this->types)) {
            $iter->append(Finder::create()
                ->name('*.twig')
                ->in($this->app_root.'/src/Application/AdminInterfaceBundle/Resources/views')
                ->in($this->app_root.'/src/Application/AgentBundle/Resources/views')
                ->in($this->app_root.'/src/Application/DeskPRO/Resources/views')
                ->in($this->app_root.'/src/Application/EmailBundle/Resources/views')
                ->in($this->app_root.'/src/Application/ReportsInterfaceBundle/Resources/views')
                ->in($this->app_root.'/src/DeskPRO/Bundle/AppBundle/Resources/views')
                ->in($this->app_root.'/src/DeskPRO/Bundle/PortalBundle/Resources/views')
                ->in($this->app_root.'/src/DeskPRO/Bundle/PortalBundle/Themes')
                ->getIterator());
        }

        if (in_array('php', $this->types)) {
            $iter->append(Finder::create()
                ->name('*.php')
                ->in($this->app_root.'/src/Application')
                ->in($this->app_root.'/src/Cloud')
                ->in($this->app_root.'/src/DeskPRO/Bundle')
                ->getIterator());
        }

        return $iter;
    }

    /**
     * @return array
     */
    public function getUseInfo()
    {
        $files = $this->getTplList();

        $phrase_use_counts = [];
        $uses              = [];
        $skip_pids         = [];

        foreach ($files as $f) {
            $content = file_get_contents($f->getRealPath());

            foreach ($this->phrase_ids as $id) {
                if (isset($skip_pids[$id])) {
                    continue;
                }
                if ($this->exclude_dynamic && $this->isDynamicPhrase($id)) {
                    $skip_pids[$id] = true;
                    continue;
                }
                if ($this->limit && isset($phrase_use_counts[$id]) && $phrase_use_counts[$id] >= $this->limit) {
                    $skip_pids[$id] = true;
                    continue;
                }
                if (!isset($phrase_use_counts[$id])) {
                    $phrase_use_counts[$id] = 0;
                }
                if (strpos($content, $id) !== false) {
                    if (!isset($uses[$id])) {
                        $uses[$id] = [];
                    }
                    $uses[$id][] = str_replace($this->app_root, '', $f->getRealPath());
                    ++$phrase_use_counts[$id];
                }
            }
        }

        return [
            'phrase_uses'   => $uses,
            'phrase_counts' => $phrase_use_counts,
        ];
    }

    /**
     * @param string $pid
     *
     * @return bool
     */
    private function isDynamicPhrase($pid)
    {
        static $prefix_re;

        if ($prefix_re === null) {
            $prefix_re = '/^('.implode('|', array_map('preg_quote', [
                'adm.agents.perm_',
                'adm.email_templates.',
                'adm.settings.reset_demo_',
                'dmin.emailtpl_desc.',
                'admin.languages.phrasegroup_',
                'admin.portal.color_',
                'agent.prefs.',
                'agent.time.',
                'api.error_codes.',
            ])).')/';
        }

        if (preg_match($prefix_re, $pid)) {
            return true;
        }

        return false;
    }
}
