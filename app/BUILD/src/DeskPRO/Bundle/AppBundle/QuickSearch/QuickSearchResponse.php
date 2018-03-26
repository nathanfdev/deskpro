<?php

namespace DeskPRO\Bundle\AppBundle\QuickSearch;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * Class QuickSearchResponse.
 */
class QuickSearchResponse
{
    /**
     * @JMS\SerializedName("grouped_results")
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\QuickSearch\QuickSearchContext>")
     *
     * @var ArrayCollection
     */
    private $contexts;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->contexts = new ArrayCollection();
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function createContext($type)
    {
        $context = new QuickSearchContext($type, $this);
        $this->contexts->offsetSet($context->getType(), $context);

        return $this;
    }

    /**
     * @param string $type
     *
     * @return QuickSearchContext
     */
    public function getContext($type)
    {
        return $this->contexts->get($type);
    }

    /**
     * @return QuickSearchContext[]
     */
    public function getContexts()
    {
        return $this->contexts;
    }
}
