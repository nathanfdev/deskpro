<?php

namespace DeskPRO\Bundle\AppBundle\Entity\ObjectAlias;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface AliasesOwner.
 */
interface AliasesOwner
{
    /**
     * @param AbstractAlias $alias
     */
    public function addAlias(AbstractAlias $alias);

    /**
     * @param AbstractAlias $alias
     */
    public function removeAlias(AbstractAlias $alias);

    /**
     * @return ArrayCollection|AbstractAlias[]
     */
    public function getAliases();
}
