<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\People\Helpers;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use Orb\Util\Arrays;
use Symfony\Component\Routing\RouterInterface;

/**
 * Helper added to People who are agents, works with agent-specific stuff.
 *
 * // PERF: Team related things: Store a flag somewhere to see if any teams exist in the db.
 * // if they dont, all team realted stuff can be optimized a bit
 */
class Agent extends \Application\DeskPRO\Domain\DomainObject implements \Orb\Helper\ShortCallableInterface
{
    /** @var Entity\Person */
    protected $person;
    /** @var array|null */
    protected $_access = null;
    /** @var array|null */
    protected $_agent_teams = null;
    /** @var array|null */
    protected $_agent_team_ids = null;
    /** @var array */
    protected $_snippets;
    /** @var Entity\TicketMacro */
    protected $_macros;

    /** @var array|null */
    protected $_dep_allowed_ids = null;
    /** @var array|null */
    protected $_dep_disallowed_ids = null;

    public function __construct(Entity\Person $person)
    {
        $this->person = $person;

        if (!$this->person['is_agent']) {
            $this->person = null;
            throw new \Exception('The agent helper is only applicable on agents');
        }
    }

    public function _getThis()
    {
        return $this;
    }

    public function getShortCallableNames()
    {
        return [
            'getAgent' => '_getThis',
            'agent'    => '_getThis',

            'teams'    => 'getTeams',
            'getTeams' => 'getTeams',

            'team'    => 'getTeam',
            'getTeam' => 'getTeam',

            'isSingleTeam'    => 'isSingleTeam',
            'getIsSingleTeam' => 'isSingleTeam',
            'countTeams'      => 'countTeams',
            'getCountTeams'   => 'countTeams',
            'hasTeams'        => 'hasTeams',
            'getHasTeams'     => 'hasTeams',

            'getSignature'      => 'getSignature',
            'getSignatureHtml'  => 'getSignatureHtml',
            'getTweetSignature' => 'getTweetSignature',
        ];
    }

    /**
     * Get the permissions helper.
     *
     * @return \Application\DeskPRO\People\Helpers\AgentPermissions
     */
    public function getPermissions()
    {
        if ($this->_permissions !== null) {
            return $this->_permissions;
        }

        $this->_permissions = new AgentPermissions($this->person);

        return $this->_permissions;
    }

    /**
     * Get a collection of teams the user is part of.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTeams()
    {
        if ($this->_agent_teams !== null) {
            return $this->_agent_teams;
        }

        try {
            $this->_agent_teams = App::$container->getAgentData()->getTeamsForAgent($this->person);
        } catch (\InvalidArgumentException $e) {
            $this->_agent_teams = [];
        }

        return $this->_agent_teams;
    }

    /**
     * Get an array of team IDs the user is part of.
     *
     * @return array
     */
    public function getTeamIds()
    {
        if ($this->_agent_team_ids !== null) {
            return $this->_agent_team_ids;
        }

        $this->_agent_team_ids = [];
        foreach ($this->getTeams() as $team) {
            $this->_agent_team_ids[] = $team['id'];
        }

        return $this->_agent_team_ids;
    }

    /**
     * Count how many teams the user belongs to.
     *
     * @return int
     */
    public function countTeams()
    {
        return count($this->getTeams());
    }

    /**
     * Does the user belong to exactly 1 team?
     *
     * @return bool
     */
    public function isSingleTeams()
    {
        if ($this->countTeams() == 1) {
            return true;
        }

        return false;
    }

    /**
     * Does this user belong to at least one team?
     *
     * @return bool
     */
    public function hasTeams()
    {
        return $this->countTeams() > 0;
    }

    /**
     * Get the persons team. If a person has more than one team ID, then this
     * will return the first.
     *
     * @return int
     */
    public function getTeam()
    {
        $teams = $this->getTeams();
        if (!$teams) {
            return 0;
        }

        $t = array_shift($teams);

        return $t;
    }

    /**
     * Check if the user is part of a specific team.
     *
     * @param $team_id
     *
     * @return bool
     */
    public function isTeamMember($team_id)
    {
        $this->getTeams();
        if (isset($this->_agent_teams[$team_id])) {
            return true;
        }

        return false;
    }

    /**
     * Add the user to a team.
     *
     * @param \Application\DeskPRO\Entity\AgentTeam $team
     */
    public function addToTeam(Entity\AgentTeam $team)
    {
        return $team->addPerson($this);
    }

    /**
     * Check if the user is allowed to use a particular department.
     *
     * @param int|Department $dep
     *
     * @return bool
     */
    public function isDepartmentAllowed($dep)
    {
        if ($dep instanceof Entity\Department) {
            $dep = $dep['id'];
        }

        return in_array($dep, $this->getAllowedDepartments());
    }

    /**
     * Get an array of departments the user isn't allowed to see.
     *
     * @return array
     */
    public function getDisallowedDepartmentIds()
    {
        if ($this->_dep_disallowed_ids !== null) {
            return $this->_dep_disallowed_ids;
        }

        $all_ids     = App::getDataService('Department')->getIds();
        $allowed_ids = $this->getAllowedDepartments();

        $disallowed_ids = array_diff($all_ids, $allowed_ids);

        $this->_dep_disallowed_ids = $disallowed_ids;

        return $this->_dep_disallowed_ids;
    }

    /**
     * Get an array of departments the user is allowed to see.
     *
     * @return array
     */
    public function getAllowedDepartmentIds()
    {
        if ($this->_dep_allowed_ids !== null) {
            return $this->_dep_allowed_ids;
        }

        $this->_dep_allowed_ids = [];
        foreach ($this->_access['departments'] as $dep) {
            $this->_dep_allowed_ids[] = $dep['id'];
        }

        return $this->_dep_allowed_ids;
    }

    /**
     * Gets the agent's text signature.
     *
     * @return string
     */
    public function getSignature()
    {
        $sig = trim($this->person->getPref('agent.ticket_signature'));
        if ($sig) {
            return $sig;
        }

        $sig_html = $this->person->getPref('agent.ticket_signature_html');
        if ($sig_html) {
            return \Orb\Util\Strings::convertWysiwygHtmlToText($sig_html);
        }

        return '';
    }

    /**
     * Gets the agent's HTML signature.
     *
     * @return string
     */
    public function getSignatureHtml()
    {
        $sig_html = $this->person->getPref('agent.ticket_signature_html');
        if (!$sig_html) {
            $sig = $this->person->getPref('agent.ticket_signature');
            if ($sig) {
                $sig_html = '<p class="dp-signature-start">'.nl2br(htmlspecialchars(trim($sig))).'</p>';
            }
        }

        if ($sig_html) {
            $fn = function ($m) {
                /*
                 * @Todo ensure the proper brand is stacked here
                 */
                $url = App::getContainer()->getBrandSetting('core.deskpro_url');
                $url .= ltrim(App::getRouter()->generate('serve_blob', ['blob_auth_id' => $m[1], 'filename' => $m[2]], RouterInterface::ABSOLUTE_PATH), '/');

                return sprintf('<img src="%s" title="%s" class="dp-signature-image" alt="%s" />',
                    $url, htmlspecialchars($m[2]), htmlspecialchars($m[0])
                );
            };

            return preg_replace_callback('#\[attach:signature_image:(.*?):(.*?)\]#', $fn, $sig_html);
        }

        return '';
    }

    /**
     * @param bool $asArray
     *
     * @return array
     */
    public function getGroupedSnippets($asArray = false)
    {
        if ($this->_snippets === null) {
            if (App::$container->get('deskpro.feature_flags')->hasBeta('new_snippets')) {
                $this->_snippets = App::getOrm()->getRepository(Snippet::class)
                    ->getSnippetsForAgent($this->person, 'ticket');
            } else {
                $this->_snippets = App::getOrm()->getRepository(Entity\TextSnippet::class)
                    ->getSnippetsForAgent('tickets', $this->person);

                $snippetsFlat = [];
                $catsFlat     = [];
                foreach ($this->_snippets as $group) {
                    $snippetsFlat = array_merge($snippetsFlat, $group['snippets']);
                    $catsFlat[]   = $group['category'];
                }
                foreach (App::getContainer()->getLanguageData()->getAll() as $lang) {
                    App::getContainer()->getObjectLangRepository()->preloadObjectCollection($lang, $snippetsFlat);
                    App::getContainer()->getObjectLangRepository()->preloadObjectCollection($lang, $catsFlat);
                }
            }
        }

        if (!App::$container->get('deskpro.feature_flags')->hasBeta('new_snippets')) {
            if ($asArray) {
                $ret = [];
                foreach ($this->_snippets as $group) {
                    $gRow = ['category' => ['id' => $group['category']->id, 'title' => $group['category']->title], 'snippets' => []];

                    foreach ($group['snippets'] as $s) {
                        $sRow = ['id' => $s->id, 'title' => $s->title];

                        if (empty($sRow['title'])) {
                            $sLangs = App::getContainer()->getObjectLangRepository()->getLoadedRecs($s);
                            if (!empty($sLangs['title'])) {
                                $sRow['title'] = Arrays::getFirstItem($sLangs['title'])->value;
                            }
                        }

                        if (!empty($sRow['title'])) {
                            $gRow['snippets'][] = $sRow;
                        }
                    }

                    if (!empty($gRow['snippets'])) {
                        usort($gRow['snippets'], function ($a, $b) {
                            return strcmp($a['title'], $b['title']);
                        });
                        $ret[] = $gRow;
                    }
                }

                usort($ret, function ($a, $b) {
                    return strcmp($a['category']['title'], $b['category']['title']);
                });

                return $ret;
            }
        }

        return $this->_snippets;
    }

    /**
     * @return \Application\DeskPRO\Entity\TicketMacro[]
     */
    public function getMacros()
    {
        if ($this->_macros !== null) {
            return $this->_macros;
        }

        $this->_macros = App::getOrm()->getRepository('DeskPRO:TicketMacro')->getMacrosForPerson($this->person);

        return $this->_macros;
    }

    /**
     * Returns a structured array of macros for use in a menu.
     *
     * Example: TOP.
     *
     * @return array
     */
    public function getMacrosStructured()
    {
        $macros = $this->getMacros();

        $struct = [
            'items'     => [],
            'sub_menus' => [],
        ];

        foreach ($macros as $m) {
            $parts = $m->getTitleParts();
            array_pop($parts);

            $last = &$struct;
            foreach ($parts as $p) {
                if (!isset($last['sub_menus'][$p])) {
                    $last['sub_menus'][$p] = ['items' => [], 'sub_menus' => []];
                }
                $last = &$last['sub_menus'][$p];
            }

            $last['items'][] = $m;
        }

        $fn = function ($col) use (&$fn) {
            $items = [];

            foreach ($col['items'] as $itm) {
                $p       = $itm->getTitleParts();
                $items[] = ['type' => 'item', 'title' => array_pop($p), 'item' => $itm];
            }
            foreach ($col['sub_menus'] as $title => $sub) {
                $sub_items = $fn($sub);
                $items[]   = ['type' => 'collection', 'title' => $title, 'items' => $sub_items];
            }

            usort($items, function ($a, $b) {
                return strcmp($a['title'], $b['title']);
            });

            return $items;
        };

        return $fn($struct);
    }

    /**
     * Gets the agent's Tweet signature.
     *
     * @return string
     */
    public function getTweetSignature()
    {
        return (string) $this->person->getPref('agent.tweet_signature');
    }

    public function getPrimaryTeam()
    {
        return $this->person->getPrimaryTeam();
    }
}
