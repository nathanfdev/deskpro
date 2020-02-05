<?php

namespace DeskPRO\Bundle\AppBundle\Features;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Usergroup;
use DeskPRO\Bundle\MessengerBundle\Service\MessengerSettingsResolver;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MessengerFeature.
 */
class MessengerFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'messenger';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Messenger (Experimental)';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Improved widget messenger for customers.';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return <<<'HTML'
Enabling Messenger v2 will switch on Messenger API on your helpdesk.

HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return <<<'HTML'
Disabling Messenger v2 will switch off Messenger API on your helpdesk.
HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailability()
    {
        return [self::AVAILABLE_EVERYWHERE];
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabledOnInstall()
    {
        return true;
    }

    public function beforeEnable(ContainerInterface $container, $newInstall = false)
    {
        /** @var EntityManager $em */
        $em         = $container->get('doctrine.orm.default_entity_manager');
        $usergroups = $em->getRepository(Usergroup::class)->findBy(['is_agent_group' => false, 'is_enabled' => true]);
        $brands     = $em->getRepository(Brand::class)->findAll();
        $db         = $em->getConnection();
        $ug         = serialize(array_map(function ($u) {
            return $u->getId();
        }, $usergroups));
        foreach ($brands as $brand) {
            $db->executeUpdate('
                INSERT IGNORE INTO settings_brand
                    (name, value, brand_id)
                VALUES
                    (?, ?, ?)
            ', [MessengerSettingsResolver::CHAT_USERGROUPS, $ug, $brand->getId()]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function needAgentReload()
    {
        return false;
    }
}
