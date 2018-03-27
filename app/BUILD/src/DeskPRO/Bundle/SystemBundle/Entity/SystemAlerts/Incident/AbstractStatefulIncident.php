<?php

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractStatefulIncident.
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractStatefulIncident extends AbstractIncident implements StatefulIncident
{
    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $resolved = false;

    /**
     * {@inheritdoc}
     */
    public function isResolved()
    {
        return $this->resolved;
    }

    /**
     * {@inheritdoc}
     */
    public function setResolved($resolved)
    {
        $this->resolved = $resolved;
    }
}
