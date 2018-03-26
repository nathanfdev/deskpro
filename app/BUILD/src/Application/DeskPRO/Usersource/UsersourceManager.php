<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource;

use Application\DeskPRO\App;
use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\Adapter\IdentityFinderInterface;
use Doctrine\ORM\EntityManager;

class UsersourceManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\Entity\Usersource[]
     */
    protected $usersources = null;

    /**
     * @var DeskproContainer
     */
    private $container;

    /**
     * Container.
     *
     * @param EntityManager    $em
     * @param DeskproContainer $container
     */
    public function __construct(EntityManager $em, DeskproContainer $container)
    {
        $this->em        = $em;
        $this->container = $container;
    }

    public function ensureSsoSettings(Usersource $usersource)
    {
        if ($usersource->is_sso_auto || $usersource->is_sso_background) {
            if ($usersource->type === Usersource::TYPE_USER) {
                $sources = $this->getAll()->configuredForUsers(true);
            } else {
                $sources = $this->getAll()->configuredForAgents(true);
            }

            /** @var \Application\DeskPRO\Entity\Usersource $source */
            foreach ($sources as $source) {
                if ($source->id != $usersource->id) {
                    $source->disableSso();
                    if ($source->app) {
                        $this->container->getSystemService('app_manipulator')->disableSso($source->app);
                    }
                }
            }

            $this->em->flush();
        }
    }

    /**
     * Find a person in a USER usersource based on an email address.
     *
     * @param string $input this can actually be any input (but is usually email)
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    public function findPersonByEmail($input)
    {
        $identityUsersources = $this->getAll()->configuredForUsers()->withCapability(UsersourceInfo::CAPABILITY_FIND_IDENTITY);

        /** @var \Application\DeskPRO\Entity\Usersource $usersource */
        foreach ($identityUsersources as $usersource) {
            $adapter = $usersource->getAdapter();

            if ($adapter instanceof IdentityFinderInterface) {
                try {
                    if ($identity = $adapter->findIdentityByInput($input)) {
                        // if the usersource can return a person directly, return that now
                        if ($identity instanceof Person) {
                            return $identity;
                        }

                        // otherwise it must be an identity, lets get the person:
                        $login_processor = new LoginProcessor($usersource, $identity);

                        return $login_processor->getPerson();
                    }
                } catch (\Exception $e) {
                }
            }
        }

        return;
    }

    /**
     * Get all installed usersources.
     *
     * @return \Application\DeskPRO\Entity\Usersource[]
     *
     * @deprecated use getAll() and filter with UsersourceCollection as needed
     */
    public function getUsersources()
    {
        if ($this->usersources !== null) {
            return $this->usersources;
        }

        $this->usersources = $this->em->getRepository('DeskPRO:Usersource')->getAllUsersources(true);

        return $this->usersources;
    }

    /**
     * Get all usersources for the agent/admin area.
     *
     * @return \Application\DeskPRO\Usersource\UsersourceCollection|\Application\DeskPRO\Entity\Usersource[]
     */
    public function getAll()
    {
        return new UsersourceCollection(
            $this->em->getRepository('DeskPRO:Usersource')->getAll()
        );
    }

    /**
     * @param string $type
     *
     * @return \Application\DeskPRO\Entity\Usersource[]
     *
     * @deprecated use getAll() and filter with UsersourceCollection as needed
     */
    public function getUsersourcesOfType($type)
    {
        $ret = [];

        $type = strtolower($type);

        foreach ($this->getUsersources() as $us) {
            if (strtolower($us->source_type) == $type) {
                $ret[$us->id] = $us;
            }
        }

        return $ret;
    }

    /**
     * Get usersources with a certain capability.
     *
     * @param $capability
     *
     * @return \Application\DeskPRO\Entity\Usersource[]
     *
     * @deprecated use getAll() and filter with UsersourceCollection as needed
     */
    public function getWithCapability($capability)
    {
        $ret = [];
        foreach ($this->getUsersources() as $us) {
            if ($us->getAdapter()->isCapable($capability)) {
                $ret[] = $us;
            }
        }

        return $ret;
    }

    /**
     * @return string
     *
     * @deprecated this shouldn't be used anymore, try to eliminate it form the codebase and use twig extension instead
     */
    public function renderView(Usersource $usersource, $type, array $params = [])
    {
        $params['usersource'] = $usersource;

        $name = $usersource->getAdapter()->getTypename();
        $tpl  = 'DeskPRO:Auth:'.$name.'-'.$type.'.html.twig';

        if (!isset($params['type'])) {
            $params['type'] = 'user';
        }

        $html = App::getTemplating()->render($tpl, $params);

        return $html;
    }

    public function getById($sso_usersource_id)
    {
        return $this->usersources = $this->em->getRepository('DeskPRO:Usersource')->find($sso_usersource_id);
    }

    /**
     * @param Usersource $usersource
     * @param \DateTime  $last_updated
     *
     * @return \Application\DeskPRO\Entity\PersonUsersourceAssoc[]
     */
    public function findAssociationsUpdatedBefore(Usersource $usersource, \DateTime $last_updated)
    {
        /** @var \Application\DeskPRO\EntityRepository\PersonUsersourceAssoc $assoc_repo */
        $assoc_repo = $this->em->getRepository('DeskPRO:PersonUsersourceAssoc');

        return $assoc_repo->getAssociationsUpdatedBefore($usersource, $last_updated);
    }
}
