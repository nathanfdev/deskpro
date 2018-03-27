<?php

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
     *                             check if a phrase is used anywhere). Set 0 to have no limit
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

        if (in_array('js', $this->types)) {
            $iter->append(Finder::create()
                ->name('*.js')
                ->in($this->app_root.'/../../www/assets/BUILD/pub/src/DeskPRO/Bundle/PortalBundle')
                ->in($this->app_root.'/../../www/assets/BUILD/pub/src/DeskPRO/Bundle/WidgetBundle')
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
     * @return array
     */
    public function getPhrasesByPrefix()
    {
        $files = $this->getTplList();

        $phrases   = [];
        $skip_pids = [];

        foreach ($files as $f) {
            $content = file_get_contents($f->getRealPath());

            foreach ($this->phrase_ids as $id) {
                if ($this->exclude_dynamic && $this->isDynamicPhrase($id)) {
                    $skip_pids[$id] = true;
                    continue;
                }
                if (preg_match_all('/'.preg_quote($id).'[-_0-9a-zA-Z\.]+/', $content, $matches) > 0) {
                    $phrases = array_merge($phrases, $matches[0]);
                }
            }
        }

        return array_unique($phrases);
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
                'admin.emailtpl_desc.',
                'admin.languages.phrasegroup_',
                'admin.portal.color_',
                'agent.prefs.',
                'agent.time.',
                'api.error_codes.',
                'user.lang.',
                'user.error.',
                'portal.forms.',
                'portal.chat.',
                'portal.email_subjects.',
                'portal.emails.',
                'portal.error.',
                'user.time.',
            ])).')/';
        }

        if (preg_match($prefix_re, $pid)) {
            return true;
        }

        return false;
    }
}
