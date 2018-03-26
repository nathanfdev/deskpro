<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\People\Helpers;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * This helps working with agent preferences.
 */
class AgentPrefs implements \Orb\Helper\ShortCallableInterface
{
    /** @var \Application\DeskPRO\Entity\Person */
    protected $person;
    /** @var array */
    protected $prefs = [];

    /** @var array */
    protected $loaded_prefs = [];
    /** @var array */
    protected $loaded_pref_prefixes = [];

    /** @var array */
    protected $preload_ids = ['agent.ticket_signature', 'agent.ticket_signature_html'];
    /** @var array */
    protected $preload_prefixes = ['agent.ui.flag.'];

    public function __construct(Entity\Person $person)
    {
        $this->person = $person;
    }

    public function getShortCallableNames()
    {
        return [
            'getAgentPref'       => 'getAgentPref',
            'getAgentNamedPrefs' => 'getNamedPrefs',
        ];
    }

    public function preload()
    {
        if (!$this->preload_ids && !$this->preload_prefixes) {
            return;
        }

        $params = ['p' => $this->person];
        $sql    = 'SELECT pref FROM DeskPRO:PersonPref pref INDEX BY pref.name WHERE pref.person = :p AND (';

        if ($this->preload_ids) {
            $sql .= 'pref.name IN (:ids)';
            $params['ids'] = $this->preload_ids;
            foreach ($this->preload_ids as $id) {
                $this->loaded_prefs[] = $id;
            }
        }
        if ($this->preload_prefixes) {
            if ($this->preload_ids) {
                $sql .= ' OR ';
            }

            $x     = 0;
            $parts = [];
            foreach ($this->preload_prefixes as $prefix) {
                $qname                        = 'p'.$x;
                $parts[]                      = 'pref.name LIKE :'.$qname;
                $params[$qname]               = $prefix.'%';
                $this->loaded_pref_prefixes[] = $prefix;
            }

            $sql .= implode(' OR ', $parts);
        }

        $sql .= ')';

        $results     = App::getOrm()->createQuery($sql)->execute($params);
        $this->prefs = array_merge($this->prefs, $results);

        $this->preload_ids = $this->preload_prefixes = [];
    }

    public function isPrefLoaded($name)
    {
        if (isset($this->loaded_prefs[$name])) {
            return true;
        }

        foreach ($this->loaded_pref_prefixes as $prefix) {
            if (strpos($name, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function getPref($name)
    {
        $this->preload();
        if (!isset($this->prefs[$name]) && !$this->isPrefLoaded($name)) {
            $sql                       = 'SELECT pref FROM DeskPRO:PersonPref pref WHERE pref.person = ?0 AND pref.name = ?1';
            $this->prefs[$name]        = App::getOrm()->createQuery($sql)->execute([$this->person, $name]);
            $this->loaded_prefs[$name] = true;
        }

        if (!$this->prefs[$name]) {
            return;
        }

        return $this->prefs[$name]->getValue();
    }

    public function getNamedPrefs()
    {
        $this->preload();

        if (func_num_args() == 1) {
            $names   = [];
            $names[] = func_get_arg(0);
        } else {
            $names = func_get_args();
        }

        $not_found = [];

        foreach ($names as $n) {
            if (!$this->isPrefLoaded($n)) {
                $not_found[]               = $n;
                $this->loaded_prefs[$name] = true; // loading it in a sec
            }
        }

        if ($not_found) {
            $sql         = 'SELECT pref FROM DeskPRO:PersonPref pref WHERE pref.person = ?0 AND pref.name IN (?2)';
            $results     = App::getOrm()->createQuery($sql)->execute([$this->person, $not_found]);
            $this->prefs = array_merge($this->prefs, $results);
        }

        $ret = [];
        foreach ($names as $n) {
            if (isset($this->prefs[$n])) {
                $ret[$n] = $this->prefs[$n]->getValue();
            } else {
                $ret[$n] = null;
            }
        }

        return $ret;
    }
}
