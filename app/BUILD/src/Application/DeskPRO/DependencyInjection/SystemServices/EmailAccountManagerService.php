<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\DeskPRO\Email\EmailAccount\IncomingAccount\FetcherStorageFactory;
use Application\DeskPRO\Email\EmailAccount\Repository\EmailAccountRepository;
use Application\DeskPRO\Entity\Brand;

class EmailAccountManagerService
{
    public static function create(DeskproContainer $container)
    {
        $repos           = new EmailAccountRepository($container->getEm());
        $tr_factory      = $container->get('email.raw_transport_factory');
        $fetcher_factory = new FetcherStorageFactory();

        $manager               = new EmailAccountManager($repos, $tr_factory, $fetcher_factory, $container->get('dp_enc'));
        $brands                = $container->get('doctrine.orm.default_entity_manager')->getRepository(Brand::class)->findAll();
        $defaultBrand          = (int) $container->getSetting('portal.default_brand');
        $defaultAddresses      = [];
        $brandSettingsResolver = $container->get('brand_aware_settings_resolver');
        foreach ($brands as $brand) {
            $accountAddress                    = $brandSettingsResolver->getSetting('core.default_from_email', $brand);
            $account                           = $manager->findAccountForEmailAddress($accountAddress, 'is_enabled | with_transport');
            $defaultAddresses[$brand->getId()] = $account;
            if ($brand->getId() === $defaultBrand) {
                $defaultAddresses['default'] = $account;
            }
        }

        if ($defaultAddresses) {
            $manager->setDefaultOutAccounts(array_filter($defaultAddresses, 'boolval'));
        }

        return $manager;
    }
}
