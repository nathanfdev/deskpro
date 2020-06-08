<?php

namespace DeskPRO\Bundle\AppBundle\Features;

use Application\DeskPRO\Entity\Blob;
use DeskPRO\Bundle\AppBundle\Templating\LegacyThemeBackup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HelpcenterFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'helpcenter';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Help Center';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Modern looking and improved theme for the portal.';
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraInfoContent(ContainerInterface $container)
    {
        /** @var EntityManager $em */
        $em   = $container->get('doctrine.orm.default_entity_manager');
        $blob = $em->getRepository(Blob::class)->findOneBy([
            'sys_name' => LegacyThemeBackup::BACKUP_SYS_NAME,
        ]);

        if (!$blob) {
            return '';
        }

        $count = preg_replace('/^(\d+).*/', '$1', $blob->getFilename());
        $url   = $blob->getDownloadUrl();

        if ($this->isEnabled()) {
            return '<a href="'.$url.'">Click here to download</a> your '.$count.' old portal templates.';
        }

        $content  = '<p>You have '.$count.' customised templates that you will need to manually update if you enable this feature.</p>';
        $content .= '<a href="'.$url.'">Click here to download items.</a>';

        return $content;
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
    public function needAgentReload()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return <<<'HTML'
Enable Helpcenter theme<br /><br />

!This theme should not be enabled on production as it is still under development!

This will allow you to select the helpcenter theme in the admin interface.
HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return <<<'HTML'
Disable helpcenter theme.
HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabledOnInstall()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateReleased()
    {
        return new \DateTime('2020-06-01');
    }

    /**
     * {@inheritdoc}
     */
    public function getDueDate()
    {
        return new \DateTime('2020-09-01');
    }

    /**
     * {@inheritdoc}
     */
    public function canBeDisabled()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeEnable(ContainerInterface $container, $newInstall = false)
    {
        $container->get('legacy_template_backup')->refreshLegacyTemplatesBackup();
    }
}
