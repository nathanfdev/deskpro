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
 * Helper keeps track of "help messages" which are displayed until dismissed, and never seen again.
 */
class HelpMessages implements \Orb\Helper\ShortCallableInterface
{
    const ALL = '__ALL__';

    /** @var \Application\DeskPRO\Entity\Person */
    protected $person;
    /** @var Entity\PersonPref */
    protected $pref;
    /** @var string */
    protected $pref_name;

    public function __construct(Entity\Person $person)
    {
        $this->person    = $person;
        $this->pref_name = 'ui.dismissed-help-messages';
    }

    public function setPrefName($pref_name)
    {
        $this->pref_name = $pref_name;
    }

    protected function _initPref()
    {
        if ($this->pref !== null) {
            return;
        }

        $this->pref = $this->person->getPref($this->pref_name);
        if (!$this->pref) {
            $this->pref          = $this->person->addPreference($this->pref_name);
            $this->pref['value'] = [];
            $person              = $this->person;

            App::getOrm()->transactional(function ($em) use ($person) {
                $em->persist($person);
                $em->flush();
            });
        }
    }

    public function _getThis()
    {
        return $this;
    }

    public function getShortCallableNames()
    {
        return [
            'getHelpMessages'   => '_getThis',
            'shouldShowMessage' => 'shouldShowMessage',
        ];
    }

    public function getDismissedIds()
    {
        $this->_initPref();

        return $this->pref['value'];
    }

    public function shouldShowMessage($id)
    {
        $this->_initPref();

        return !$this->isDismissed($id);
    }

    public function isDismissed($id)
    {
        $this->_initPref();

        return in_array(self::ALL, $this->pref['value']) or in_array($id, $this->pref['value']);
    }

    public function dismiss($id)
    {
        if ($this->isDismissed($id)) {
            return;
        }

        $this->_initPref();

        $val   = $this->pref['value'];
        $val[] = $id;

        $this->pref['value'] = $val;

        $pref = $this->pref;
        App::getOrm()->transactional(function ($em) use ($pref) {
            $em->persist($pref);
            $em->flush();
        });
    }

    public function undismiss($id)
    {
        $this->_initPref();

        $val = $this->pref['value'];

        // Not in here
        if (($key = array_search($id, $val)) === false) {
            return;
        }

        unset($val[$key]);
        $val = array_values($val); // rekey numerically

        $this->pref['value'] = $val;

        $pref = $this->pref;
        App::getOrm()->transactional(function ($em) use ($pref) {
            $em->persist($pref);
            $em->flush();
        });
    }

    public function reset()
    {
        $this->_initPref();

        $this->pref['value'] = [];

        $pref = $this->pref;
        App::getOrm()->transactional(function ($em) use ($pref) {
            $em->persist($pref);
            $em->flush();
        });
    }
}
