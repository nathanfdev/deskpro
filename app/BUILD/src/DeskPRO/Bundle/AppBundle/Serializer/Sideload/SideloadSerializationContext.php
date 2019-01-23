<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Sideload;

use Application\DeskPRO\Entity\Person;
use JMS\Serializer\SerializationContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class SideloadSerializationContext.
 */
class SideloadSerializationContext extends SerializationContext
{
    const INCLUDE_STRATEGY_DATA   = 'data';
    const INCLUDE_STRATEGY_CUSTOM = 'custom';

    /**
     * @var SideloadStore
     */
    protected $sideloadStore;

    /**
     * Sideload types strategy.
     *
     * 'custom' - allowed types are get from 'includes'.
     * 'data'    - all types from data object, 'includes' are skipped.
     *
     * @var string
     */
    protected $includesStrategy = self::INCLUDE_STRATEGY_CUSTOM;

    /**
     * @var array
     */
    protected $includes;

    /**
     * @var bool
     */
    protected $exclusionEnabled = true;

    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * @var Person
     */
    protected $user;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var bool
     */
    protected $idsOnly = false;

    /**
     * @var bool
     */
    protected $inlineSideloads = false;

    /**
     * @var bool
     */
    protected $disabledSideloads = false;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Constructor.
     *
     * @param array                 $includes
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(array $includes = [], TokenStorageInterface $tokenStorage = null)
    {
        parent::__construct();

        $this->sideloadStore = new SideloadStore();
        $this->includes      = $includes;
        $this->tokenStorage  = $tokenStorage;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return SideloadSerializationContext
     */
    public static function createContext(ContainerInterface $container)
    {
        $request         = $container->get('request_stack')->getCurrentRequest();
        $rawIncludes     = $request->query->get('include');
        $idsOnly         = $request->query->getBoolean('ids_only', false);
        $inlineSideloads = $request->query->getBoolean('inline_sideloads', false);

        $context = new self(self::cleanIncludes($rawIncludes), $container->get('security.token_storage'));
        $context->setIdsOnly($idsOnly);
        $context->setInlineSideloads($inlineSideloads);
        $context->setRequest($request);

        return $context;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setExclusionEnabled($enabled = true)
    {
        $this->exclusionEnabled = (bool) $enabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExclusionStrategy()
    {
        if ($this->exclusionEnabled) {
            return parent::getExclusionStrategy();
        }

        return;
    }

    /**
     * @return SideloadStore
     */
    public function getSideloadStore()
    {
        return $this->sideloadStore;
    }

    /**
     * @return array
     */
    public function getIncludes()
    {
        if ($this->includesStrategy === self::INCLUDE_STRATEGY_CUSTOM) {
            return $this->includes;
        } else {
            return $this->sideloadStore->getAvailableTypes();
        }
    }

    /**
     * @param array $includes
     */
    public function setIncludes(array $includes)
    {
        $this->includes = $includes;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function hasInclude($type)
    {
        return in_array($type, $this->includes);
    }

    /**
     * @return array
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @param string $className
     *
     * @return bool
     */
    public function getMappedClass($className)
    {
        if (isset($this->mapping[$className])) {
            return $this->mapping[$className];
        }

        return false;
    }

    /**
     * @param array $mapping
     *
     * @return $this
     */
    public function setMapping(array $mapping)
    {
        $this->mapping = $mapping;

        return $this;
    }

    /**
     * @param Person $user
     *
     * @return $this
     */
    public function setUser(Person $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Person|null
     */
    public function getUser()
    {
        if ($this->user) {
            return $this->user;
        }
        if ($this->tokenStorage) {
            $person = $this->tokenStorage->getToken()->getUser();

            return $person instanceof Person ? $person : null;
        }

        return;
    }

    /**
     * @return bool
     */
    public function isIdsOnly()
    {
        return $this->idsOnly;
    }

    /**
     * @param bool $idsOnly
     *
     * @return $this
     */
    public function setIdsOnly($idsOnly)
    {
        $this->idsOnly = $idsOnly;

        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     *
     * @return $this
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @param string $requested_includes_string
     *
     * @return array
     */
    protected static function cleanIncludes($requested_includes_string)
    {
        if (null === $requested_includes_string) {
            return [];
        }

        $exploded = explode(',', $requested_includes_string);

        $cleaned = [];

        foreach ($exploded as $type) {
            $type = trim($type);

            if (!empty($type)) {
                $cleaned[] = $type;
            }
        }

        return $cleaned;
    }

    /**
     * @return string
     */
    public function getIncludesStrategy()
    {
        return $this->includesStrategy;
    }

    /**
     * @param string $includesStrategy
     *
     * @return $this
     */
    public function setIncludesStrategy($includesStrategy)
    {
        $this->includesStrategy = $includesStrategy;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInlineSideloads()
    {
        return $this->inlineSideloads;
    }

    /**
     * @param bool $inlineSideloads
     *
     * @return $this
     */
    public function setInlineSideloads($inlineSideloads)
    {
        $this->inlineSideloads = $inlineSideloads;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabledSideloads()
    {
        return $this->disabledSideloads;
    }

    /**
     * @param bool $disabledSideloads
     *
     * @return $this
     */
    public function setDisabledSideloads($disabledSideloads)
    {
        $this->disabledSideloads = $disabledSideloads;

        return $this;
    }
}
