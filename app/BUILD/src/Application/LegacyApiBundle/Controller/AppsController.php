<?php

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\App\AppManipulatorContext;
use Application\DeskPRO\App\Native\NativeAppsSync;
use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestContext;
use Application\DeskPRO\App\Package\Package;
use Application\DeskPRO\App\Package\PackageInstaller;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\AppPackage;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Monolog\Logger;
use Application\DeskPRO\Service\JIRA;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\App;
use DeskPRO\Bundle\AppBundle\Metrics\InterestingEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppStoreBundle\Domain\AppBundleValidator;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters\BundleFileHandlingStrategyZip;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\ApplicationManagerService;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppManifestReader;
use DeskPRO\Component\Filesystem\SafeFile;
use DpSys\LowError\SystemErrorHandler;
use Imagine\Image\Box as ImageBox;
use Orb\Util\Arrays;
use Orb\Util\DpStrings;
use Orb\Util\Strings;
use Orb\Zip\ZipException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AppsController.
 *
 * @ApiModes("all")
 */
class AppsController extends AbstractController
{
    //###################################################################################################################
    // list
    //###################################################################################################################

    public function listAction()
    {
        $manager = $this->container->getAppManager();

        $apps               = [];
        $installed_packages = [];
        foreach ($manager->getAllApps() as $a) {
            $apps[]                                = $a->toApiData();
            $installed_packages[$a->package->name] = true;
        }

        $packages = [];
        foreach ($manager->getAllPackages() as $p) {
            $p_data                 = $p->toApiData();
            $p_data['is_installed'] = isset($installed_packages[$p->name]);

            $packages[] = $p_data;
        }

        if ($tags = $this->in->getString('tags')) {
            $tags        = explode(',', $tags);
            $package_ids = [];

            $packages = array_filter($packages, function ($p) use ($tags, &$package_ids) {
                $has = false;
                foreach ($tags as $t) {
                    if ($p['tags'] && in_array($t, $p['tags'])) {
                        $has = true;
                        $package_ids[$p['name']] = true;
                        break;
                    }
                }

                return $has;
            });

            $apps = array_filter($apps, function ($a) use ($package_ids) {
                return isset($package_ids[$a['package_name']]);
            });
        }

        // Attach usersources to apps if they own them
        $usersources = $this->em->createQuery('
            SELECT u, a
            FROM DeskPRO:Usersource u
            LEFT JOIN u.app a
            WHERE u.app IS NOT NULL
        ')->execute();
        if ($usersources) {
            $usersources = Arrays::rekey($usersources, function ($u) {
                return $u->app->getId();
            });
            $apps = array_map(function ($a) use ($usersources) {
                if (isset($usersources[$a['id']])) {
                    $a['usersource'] = $usersources[$a['id']]->toApiData();
                }

                return $a;
            }, $apps);
        }

        // Re-index in case we filtered by tag
        $apps     = array_values($apps);
        $packages = array_values($packages);

        return $this->createApiResponse(['packages' => $packages, 'apps' => $apps]);
    }

    //###################################################################################################################
    // get-package
    //###################################################################################################################

    public function getPackageAction($name)
    {
        $manager = $this->container->getAppManager();

        if (!$manager->hasPackage($name)) {
            // app v2 package info
            $appArchive = $this->getAppV2ArchiveBundle($name);

            $app = $this->em->getRepository(App::class)->findOneBy([
                'name' => $name,
            ]);
            if ($appArchive) {
                $manifestReader = new AppManifestReader();

                $manifest = $manifestReader->readManifestFromJson($appArchive->getManifestAsString());
                $iconBlob = $this->container->get('blob.storage')->createBlobRecordFromString(
                    $appArchive->getIcon(),
                    'icon.png',
                    'image/png'
                );

                $iconBlob->setIsTemp(true);
                $this->em->persist($iconBlob);
                $this->em->flush();
            } elseif ($app) {
                $manifest = $app->getManifest();
                $iconBlob = $app->getIconAsset()->getBlob();
            } else {
                throw $this->createNotFoundException();
            }

            if ($app) {
                $apps = $this->container->get('serializer')->toArray(new ApiWrapper($app->getInstances()), new SideloadSerializationContext());
                $apps = $apps['data'];
            } else {
                $apps = [];
            }

            $data = [
                'name'         => $manifest->getName(),
                'native_name'  => $manifest->getName(),
                'title'        => $manifest->getTitle(),
                'scope'        => $appArchive ? '' : $manifest->getScope(), // v2 apps no longer use this property
                'is_installed' => $app ? $app->getInstances()->count() > 0 : false,
                'is_single'    => $manifest->isSingle(),
                'readme'       => $manifest->getDescription(),
                'readme_html'  => $manifest->getDescription(),
                'settings_def' => [],
                'assets'       => [],
                'icon_32'      => $iconBlob->getThumbnailUrl(32),
                'icon_48'      => $iconBlob->getThumbnailUrl(48),
                'icon_64'      => $iconBlob->getThumbnailUrl(64),
                'author_name'  => $manifest->getAuthor()->getName(),
                'author_email' => $manifest->getAuthor()->getEmail(),
                'author_link'  => $manifest->getAuthor()->getUrl(),
                'version_name' => $manifest->getAppVersion(),
                'apps'         => $apps,
                'app_version'  => 2,
            ];

            return $this->createApiResponse(['package' => $data]);
        }

        $package = $manager->getPackage($name);

        //------------------------------
        // Get readme
        //------------------------------

        $readme      = '';
        $readme_html = '';

        $readme_asset = $package->getTaggedAsset('readme.text');
        if ($readme_asset) {
            $readme = $this->container->getBlobStorage()->copyBlobRecordToString($readme_asset->blob);
        }

        $readme_html_asset = $package->getTaggedAsset('readme.html');
        if ($readme_html_asset) {
            $readme_html = $this->container->getBlobStorage()->copyBlobRecordToString($readme_html_asset->blob);
        }

        $data                 = $package->toApiData();
        $data['is_installed'] = false;
        $data['readme']       = $readme;
        $data['readme_html']  = $readme_html;
        $data['app_version']  = 1;

        //------------------------------
        // Get assets
        //------------------------------

        $data['assets'] = [];
        foreach ($package->assets as $asset) {
            $data['assets'][] = $asset->toApiData(false);
        }

        //------------------------------
        // Get installed app instances
        //------------------------------

        $appManager   = $this->container->getAppManager();
        $data['apps'] = [];
        foreach ($manager->getPackageApps($package->name) as $app) {
            $app             = $app->toApiData(false);
            $userUsersource  = $appManager->getUsersourceForApp($app['id'], Usersource::TYPE_USER);
            $agentUsersource = $appManager->getUsersourceForApp($app['id'], Usersource::TYPE_AGENT);
            if ($userUsersource) {
                $userUsersource = $userUsersource->toApiData();
            }
            if ($agentUsersource) {
                $agentUsersource = $agentUsersource->toApiData();
            }
            if ($package->isUsersource()) {
                $app['user_usersource']  = $userUsersource;
                $app['agent_usersource'] = $agentUsersource;
            }
            $data['apps'][] = $app;
        }

        if ($data['apps']) {
            $data['is_installed'] = true;
        }

        return $this->createApiResponse(['package' => $data]);
    }

    //###################################################################################################################
    // delete-package
    //###################################################################################################################

    public function deletePackageAction($name)
    {
        $manager = $this->container->getAppManager();

        if (!$manager->hasPackage($name)) {
            throw $this->createNotFoundException();
        }

        $package = $manager->getPackage($name);

        if ($package->native_name) {
            return $this->createApiErrorResponse('no_delete_native', "$name is a native application installed into the filesystem and cannot be deleted from the web interface");
        }

        if ($manager->getPackageApps($name)) {
            return $this->createApiErrorResponse('is_installed', "$name has one or more installed instances. Uninstall all instances then delete the app.");
        }

        // Remove all assets from blob storage
        $blob_storage = $this->container->getBlobStorage();
        foreach ($package->assets as $asset) {
            try {
                $blob_storage->deleteBlobRecord($asset->blob);
            } catch (\Exception $e) {
            }
        }

        $this->em->remove($package);
        $this->em->flush();

        return $this->createApiDeleteResponse(['old_name' => $name]);
    }

    //###################################################################################################################
    // install-package
    //###################################################################################################################

    public function installPackageAction($name)
    {
        // we are expecting the client to double url encode $name
        // in case it contains forward slashes, e.g @deskproapps/app-name
        // the actual problem can be solved by just double encoding of '/', / => %2F => %252F
        // but it is simpler on the client to double encode everything
        $name    = urldecode(urldecode($name));
        $manager = $this->container->getAppManager();

        if (!$manager->hasPackage($name)) {
            // app v2 package info
            $appArchive = $this->getAppV2ArchiveBundle($name);

            if ($appArchive) {
                $app = $this->em->getRepository(App::class)->findOneBy([
                    'name' => $name,
                ]);

                $manifestReader = new AppManifestReader();
                $manifest       = $manifestReader->readManifestFromJson($appArchive->getManifestAsString());
                $isAppUpdate    = $manifest->isSingle() && $app && $app->getInstances()->count() > 0;

                if ($isAppUpdate) {
                    $this->container->get('apps2.application_manager')->createOrUpdateAppEntity($appArchive);
                    $instance = $app->getInstances()->first();
                } else {
                    $instance = $this->container->get('apps2.application_manager')->createFirstInstance($appArchive);
                }

                $context = new SideloadSerializationContext();
                $context->setIncludes(['app']);
                $context->setInlineSideloads(true);

                $this->container->get('event_dispatcher')->dispatch(
                    LegacySystemEvent::EVENT_NAME,
                    new LegacySystemEvent('agent.ui.reload', [
                        'type'           => 'admin',
                        'person_id'      => 0,
                        'person_name'    => 'System',
                        'exclude_target' => $this->person->getId(),
                    ])
                );

                $serialized = $this->container->get('serializer')->toArray(new ApiWrapper($instance), $context);

                return $this->createApiCreateResponse(
                    array_merge(
                        $serialized,
                        [
                            'version' => 2,
                            'updated' => $isAppUpdate,
                        ]
                    ),
                    sprintf('api/v2/apps/packages/%s', $app->getId())
                );
            }

            throw $this->createNotFoundException();
        }

        $package        = $manager->getPackage($name);
        $inputTitle     = $this->in->getString('settings.dp_app.title');
        $settings       = $this->in->getCleanValueArray('settings');
        $usersourceType = $this->in->getString('usersource_type');

        if ($package->isUsersource() && !$usersourceType) {
            return $this->createApiErrorResponse(
                'invalid_argument', "$name is a usersource app and therefore you must provide the 'usersource_type' param in your request"
            );
        }

        if (!$package->isUsersource() && $package->is_single && $manager->getPackageApps($name)) {
            return $this->createApiErrorResponse('already_installed', "$name is already installed and the app has is_single=true");
        }

        $context = new AppManipulatorContext($settings, $inputTitle);
        $context->setUsersourceType($usersourceType);

        $app = $this->getAppManipulator()->installInstance($package, $context);

        if ($package->isUsersource()) {
            InterestingEvent::createAndDispatch(
                $this->get('event_dispatcher'),
                'usersource.created',
                ['area' => $context->getUsersourceType(), 'type' => $package->getId()]
            );
        }

        return $this->createApiCreateResponse(
            ['id' => $app->id],
            $this->generateUrl('api_apps_instance', ['id' => $app->id])
        );
    }

    //###################################################################################################################
    // get-instance
    //###################################################################################################################

    public function getInstanceAction($id)
    {
        $manager = $this->container->getAppManager();

        if (!$manager->hasApp($id)) {
            throw $this->createNotFoundException();
        }

        $app = $manager->getApp($id);

        $data = $app->toApiData();

        if ($app->package->getTaggedAsset('app_js')) {
            $data['with_permissions'] = true;
            $data['permissions']      = $this->em->getRepository('DeskPRO:AppInstance')->getPermissionsForInstance($app);
        }

        return $this->createApiResponse(['app' => $data]);
    }

    //###################################################################################################################
    // update-instance
    //###################################################################################################################

    public function updateInstanceAction($id)
    {
        $manager = $this->container->getAppManager();

        if (!$manager->hasApp($id)) {
            throw $this->createNotFoundException();
        }

        $app           = $manager->getApp($id);
        $settings_form = $this->in->getCleanValueArray('settings');
        $inputTitle    = $this->in->getString('settings.dp_app.title');
        $saveAssets    = $this->in->getCleanValueArray('save_assets');

        $context = new AppManipulatorContext($settings_form, $inputTitle);
        $context->setSaveAssets($saveAssets);

        $this->getAppManipulator()->updateInstance($app, $context);

        $this->db->delete('app_instance_permissions', ['app_instance_id' => $app->id]);
        if ($app->package->getTaggedAsset('app_js') && $this->in->getString('permissions.type') == 'set') {
            $app->perm_type = 'set';
            $batch          = [];

            foreach ($this->in->getArrayOfInts('permissions.usergroup_ids') as $ugid) {
                if ($this->container->getAgentGroups()->groupExists($ugid)) {
                    $batch[] = [
                        'app_instance_id' => $app->id,
                        'usergroup_id'    => $ugid,
                        'person_id'       => null,
                    ];
                }
            }
            foreach ($this->in->getArrayOfInts('permissions.person_ids') as $aid) {
                if ($this->container->getAgentData()->has($aid)) {
                    $batch[] = [
                        'app_instance_id' => $app->id,
                        'usergroup_id'    => null,
                        'person_id'       => $aid,
                    ];
                }
            }

            if ($batch) {
                $this->db->batchInsert('app_instance_permissions', $batch, true);
            }
        } else {
            $app->perm_type = 'global';
        }

        $this->em->persist($app);
        $this->em->flush();
        $this->em->getRepository('DeskPRO:Usersource')->checkAndEnableDeskpro();

        return $this->createApiSuccessResponse();
    }

    //###################################################################################################################
    // uninstall-instance
    //###################################################################################################################

    public function uninstallInstanceAction($id)
    {
        $manager = $this->container->getAppManager();

        if (!$manager->hasApp($id)) {
            throw $this->createNotFoundException();
        }

        $app = $manager->getApp($id);

        $this->getAppManipulator()->uninstallInstance($app);

        return $this->createApiDeleteResponse(['old_id' => $id]);
    }

    //###################################################################################################################
    // get-custom-assets
    //###################################################################################################################

    public function getCustomAssetsAction($id)
    {
        $manager = $this->container->getAppManager();

        if (!$manager->hasApp($id)) {
            throw $this->createNotFoundException();
        }

        $app = $manager->getApp($id);

        if (!$app->package->is_custom) {
            throw $this->createNotFoundException();
        }

        $blob_storage = $this->container->getBlobStorage();
        $assets       = [];

        foreach ($app->package->assets as $a) {
            if ($a->tag == 'js' || $a->tag == 'html' || $a->tag == 'app_js') {
                $file = $blob_storage->copyBlobRecordToString($a->blob);

                $a_info                 = $a->toApiData();
                $a_info['file_content'] = $file;

                $assets[] = $a_info;
            }
        }

        return $this->createApiResponse(['assets' => $assets]);
    }

    //###################################################################################################################
    // create-custom-app
    //###################################################################################################################

    public function createCustomAppAction()
    {
        $package               = new AppPackage();
        $package->name         = 'com.deskpro.custom.'.DpStrings::random(15, Strings::CHARS_ALPHA_I);
        $package->title        = $this->in->getString('options.title') ?: 'Untitled';
        $package->description  = $package->title;
        $package->tags         = ['custom'];
        $package->author_name  = $this->person->getDisplayName();
        $package->author_email = $this->person->getEmailAddress();
        $package->author_link  = $this->container->getBrandSetting('core.deskpro_url');
        $package->api_version  = 1;
        $package->version      = 1;
        $package->version_name = '1.0.0';
        $package->is_custom    = true;
        $package->is_single    = true;
        $package->scopes       = [AppPackage::SCOPE_AGENT];

        $this->em->persist($package);
        $blob_storage = $this->container->getBlobStorage();

        $with_blanks = [];

        $locations     = [];
        $js_files      = [];
        $html_files    = [];
        $require_files = [];
        $require_names = [];
        $tab_titles    = [];

        foreach (['ticket', 'user', 'org'] as $type) {
            $type_name = ucfirst($type);
            foreach ($this->in->getCleanValueArray('options.'.$type) as $name => $value) {
                if (!$value) {
                    continue;
                }
                if ($name == 'blank') {
                    $with_blanks[]   = ['type' => $type, 'class_name' => "{$type_name}_{$type_name}Context"];
                    $js_files[]      = ['type' => $type, 'file' => "$type_name/{$type_name}Context"];
                    $require_files[] = $package->name."/js/$type_name/{$type_name}Context";
                    $require_names[] = "{$type_name}_{$type_name}Context";
                } elseif (strpos($name, '.tab.title') !== false) {
                    $tab_titles["$type.$name"] = $value;
                } else {
                    $js_name         = ucfirst(Strings::underscoreToCamelCase(str_replace('.', '_', $name)));
                    $locations[]     = ['type' => $type, 'location' => $name, 'js_class' => $type_name.'_'.$js_name.'Controller', 'html_file' => "$type_name/".$js_name.'.html'];
                    $js_files[]      = ['type' => $type, 'file' => "$type_name/".$js_name.'Controller'];
                    $html_files[]    = "$type_name/".$js_name;
                    $require_files[] = $package->name."/js/$type_name/{$js_name}Controller";
                    $require_names[] = str_replace(' ', '_', $type_name.'_'.$js_name.'Controller');
                }
            }
        }

        //------------------------------
        // Create JS files
        //------------------------------

        foreach ($js_files as $info) {
            $file = $info['file'];
            $type = $info['type'];
            if (preg_match('#Context$#', $file)) {
                $js = "define(function () {\n\treturn {\n\t\tinit: function () {\n\t\t\t// TODO\n\t\t}\n\t};\n\n});";
            } else {
                $injects = ['$scope'];
                if ($type == 'ticket') {
                    $injects[] = '$ticket';
                    $injects[] = '$person';
                } elseif ($type == 'user') {
                    $injects[] = '$person';
                } elseif ($type == 'org') {
                    $injects[] = '$org';
                }
                $injects[] = '$http';
                $injects[] = '$el';
                $injects[] = '$app';
                $injects   = implode(', ', $injects);
                $js        = "define(function () {\n\treturn function ($injects) {\n\t\t// TODO\n\t};\n\n});";
            }

            $blob = $blob_storage->createBlobRecordFromString(
                $js,
                basename($file).'.js',
                'text/javascript'
            );

            $asset      = $package->addAssetFromBlob($blob, $file.'.js');
            $asset->tag = 'js';
            $asset->setMetadata(['group_name' => preg_replace('#Controller$#', '', str_replace('/', '_', $file))]);
            $this->em->persist($asset);
        }

        //------------------------------
        // Create HTML files
        //------------------------------

        foreach ($html_files as $file) {
            $html = 'Your HTML goes here';

            $blob = $blob_storage->createBlobRecordFromString(
                $html,
                basename($file).'.html',
                'text/html'
            );

            $asset      = $package->addAssetFromBlob($blob, $file.'.html');
            $asset->tag = 'html';
            $asset->setMetadata(['group_name' => str_replace('/', '_', $file)]);
            $this->em->persist($asset);
        }

        //------------------------------
        // Get app icons
        //------------------------------

        $sizes      = [16, 24, 32, 48, 64, 96, 128, 192, 256, 512];
        $have_sizes = [];
        $largest    = null;

        $path = DP_ROOT.'/src/Application/DeskPRO/App/Package/Resources/no-icon.png';
        $size = 256;
        $blob = $blob_storage->createBlobRecordFromFile(
            $path,
            "app_$size.png",
            'image/png'
        );

        $asset      = $package->addAssetFromBlob($blob);
        $asset->tag = "icons.app.$size";
        $this->em->persist($asset);

        $largest           = [$path, $size, $blob];
        $have_sizes[$size] = [$path, $size, $blob];

        // Missing sizes we'll just scale whatever
        // the largest icon we have
        foreach ($sizes as $size) {
            if (isset($have_sizes[$size])) {
                continue;
            }

            $image = $this->container->getImagine()->open($largest[0]);
            $image->resize(new ImageBox($size, $size));

            $blob = $blob_storage->createBlobRecordFromString(
                $image->get('png'),
                "app_$size.png",
                'image/png'
            );

            unset($image);

            $asset      = $package->addAssetFromBlob($blob);
            $asset->tag = "icons.app.$size";
            $this->em->persist($asset);

            $largest           = [$path, $size, $blob];
            $have_sizes[$size] = $blob;
        }

        //------------------------------
        // Main app.js
        //------------------------------

        $require_files = "'".implode("', '", $require_files)."'";
        $require_names = implode(', ', $require_names);

        $app_js = "define([$require_files], function ($require_names) {\n\treturn {\n\t\tinit: function () {\n";

        if ($with_blanks) {
            foreach ($with_blanks as $blank) {
                $app_js .= "\t\t\tthis.register(\"{$blank['type']}\", {$blank['class_name']});\n";
            }
        }

        foreach ($locations as $loc) {
            if (preg_match('#\.tab$#', $loc['location'])) {
                $type = $loc['type'];
                $name = $loc['location'];
                if (isset($tab_titles["$type.$name.title"])) {
                    $prop_tab_title = addslashes($tab_titles["$type.$name.title"]);
                } else {
                    $prop_tab_title = '';
                }

                $app_js .= "\t\t\tthis.registerWidgetTab(\"{$loc['type']}\", \"@{$loc['location']}\", \"$prop_tab_title\", \"{$loc['html_file']}\", {$loc['js_class']});\n";
            } else {
                $app_js .= "\t\t\tthis.registerWidget(\"{$loc['type']}\", \"@{$loc['location']}\", \"{$loc['html_file']}\", {$loc['js_class']});\n";
            }
        }

        $app_js .= "\t\t}\n\t}";

        $app_js .= "\n});";

        $blob = $blob_storage->createBlobRecordFromString(
            $app_js,
            'app.js',
            'text/javascript'
        );

        $asset      = $package->addAssetFromBlob($blob);
        $asset->tag = 'app_js';
        $this->em->persist($asset);

        $this->em->flush();

        //------------------------------
        // Create an instance of it too
        //------------------------------

        $app          = new AppInstance();
        $app->package = $package;
        $app->title   = $this->in->getString('settings.dp_app.title') ?: $package->title;

        $this->em->persist($app);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['id' => $app->id],
            $this->generateUrl('api_apps_instance', ['id' => $app->id])
        );
    }

    //###################################################################################################################
    // exec-package-action
    //###################################################################################################################

    public function execPackageAction(Request $request, $name, $action)
    {
        $manager = $this->container->getAppManager();

        if (!$manager->hasPackage($name)) {
            throw $this->createNotFoundException();
        }

        $package = $manager->getPackage($name);
        if (!$package->native_name) {
            throw $this->createNotFoundException();
        }

        $native_config = $manager->getNativePackageConfig($package);

        $handler_class = $native_config->getApiPackageRequestHandlerClass();
        if (!$handler_class) {
            throw $this->createNotFoundException();
        }

        $context = new ApiPackageRequestContext(
            $this->getContainer(),
            $request,
            $this,
            $this->person,
            $package,
            $action
        );

        $handler = new $handler_class();
        $result  = $handler->handleApiPackageRequest($context);

        return $result;
    }

    //###################################################################################################################
    // exec-app-action
    //###################################################################################################################

    public function execAppAction(Request $request, $id, $action)
    {
        $manager = $this->container->getAppManager();

        if (!$manager->hasApp($id)) {
            throw $this->createNotFoundException();
        }

        $app     = $manager->getApp($id);
        $package = $app->package;
        if (!$package->native_name) {
            throw $this->createNotFoundException();
        }

        $native_config = $manager->getNativePackageConfig($package);

        $handler_class = $native_config->getApiAppRequestHandlerClass();
        if (!$handler_class) {
            throw $this->createNotFoundException();
        }

        $context = new ApiPackageRequestContext(
            $this->getContainer(),
            $request,
            $this,
            $this->person,
            $package,
            $action
        );

        $handler = new $handler_class();
        $result  = $handler->handleApiAppRequest($context);

        return $result;
    }

    //###################################################################################################################
    // resync-packages
    //###################################################################################################################

    public function resyncPackagesAction()
    {
        $logger = new Logger('apps');
        $logger->enableSavedMessages();

        $app_syncer = new NativeAppsSync(
            $this->container,
            $this->container->getAppManager(),
            new PackageInstaller($this->container->getEm(), $this->container->getBlobStorage(), $this->container->getImagine()),
            $logger
        );

        try {
            $app_syncer->runUpdates();
            $app_syncer->runSync();

            $log = $logger->getSavedMessages();
        } catch (\Exception $e) {
            $log = $e->getMessage();
        }

        return $this->createJsonResponse([
            'success' => true,
            'log'     => $log,
        ]);
    }

    //###################################################################################################################
    // upload-package
    //###################################################################################################################

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function uploadPackageAction(Request $request)
    {
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $request->files->get('file');

        if ($file) {
            if (!$file->isValid()) {
                return $this->createApiErrorResponse('invalid_upload', 'Invalid file upload');
            }

            $temp_name = $file->getRealPath();
        } elseif ($upload_url = $this->in->getString('file_url')) {
            $temp_name = @tempnam(dp_get_tmp_dir(), 'app_upload');
            register_shutdown_function(function () use ($temp_name) {
                @unlink($temp_name);
            });

            if (!$temp_name) {
                return $this->createApiErrorResponse('copy_error', 'Failed to copy file to temp directory');
            }

            if (!@copy($upload_url, $temp_name)) {
                return $this->createApiErrorResponse('invalid_upload', 'Invalid file upload');
            }
        } else {
            return $this->createApiErrorResponse('invalid_upload', 'Invalid file upload');
        }

        $tmpdir = dp_get_tmp_dir().DIRECTORY_SEPARATOR.time().'-'.mt_rand(1000, 9999);
        if (!@mkdir($tmpdir)) {
            return $this->createApiErrorResponse('copy_error', 'Failed to create extraction directory');
        }

        register_shutdown_function(function () use ($tmpdir) {
            if (!is_dir($tmpdir)) {
                return;
            }
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tmpdir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                $path->isFile() ? @unlink($path->getPathname()) : @rmdir($path->getPathname());
            }
            @rmdir($tmpdir);
        });

        /** @var \Orb\Zip\Zip $zipper */
        $zipper = $this->container->getSystemService('zipper');

        try {
            $zipper->decompressZip($temp_name, $tmpdir);
        } catch (ZipException $e) {
            if ($e->getCode() == ZipException::BAD_FORMAT) {
                return $this->createApiErrorResponse('invalid_file', 'Invalid ZIP file -- Invalid format -- Details: '.$e->getMessage());
            } else {
                return $this->createApiErrorResponse('extract_failed', 'Invalid ZIP file -- Unknown error -- Detauls: '.$e->getMessage());
            }
        }

        $app_dir = $tmpdir;

        // See if we should go one level deep (sometimes the zip is a zip of a dir)
        if (!is_file($app_dir.'/manifest.json')) {
            $dir = dir($tmpdir);
            while (($f = $dir->read()) !== null) {
                if ($f != '.' && $f != '..' && is_dir($dir->path.'/'.$f)) {
                    $app_dir = $dir->path.'/'.$f;
                    break;
                }
            }
            $dir->close();
        }

        if (!is_file($app_dir.'/manifest.json')) {
            return $this->createApiErrorResponse('missing_manifest', 'Missing manifest.json');
        }

        // detect apps v2
        /** @var BundleFileHandlingStrategyZip $bundleReader */
        $bundleReader    = $this->container->get(BundleFileHandlingStrategyZip::class);
        $appBundle       = $bundleReader->reader($file);
        $bundleValidator = $this->container->get(AppBundleValidator::class);
        if ($bundleValidator->validateBundle($appBundle)) {
            $manifestString = SafeFile::fileGetContents($app_dir.'/manifest.json', $app_dir);
            $manifestReader = new AppManifestReader();
            $manifest       = $manifestReader->readManifestFromJson($manifestString);

            /** @var ApplicationManagerService $appsManager */
            $appsManager    = $this->container->get('apps2.application_manager');
            $installDetails = $appsManager->installBundle($appBundle);

            $context = new SideloadSerializationContext();
            $context->setIncludes(['app']);
            $context->setInlineSideloads(true);
            $serialized = $this->container->get('serializer')->toArray(new ApiWrapper($installDetails->getApp()), $context);

            return $this->createApiCreateResponse(
                array_merge(
                    $serialized,
                    [
                        'version'      => 2,
                        'package_name' => $manifest->getName(),
                        'install_type' => $installDetails->getInstallType(),
                    ]
                ),
                sprintf('/api/v2/apps/packages/%s', $installDetails->getApp()->getId())
            );
        }

        try {
            $app_package = new Package($app_dir);
        } catch (\Exception $e) {
            return $this->createApiErrorResponse('invalid_manifest', 'Invalid manifest file: '.$e->getMessage());
        }

        if ($app_package->getManifest()->getIsNative()) {
            return $this->createApiErrorResponse('invalid_native', 'Native apps cannot be uploaded using this method');
        }

        $installer = new PackageInstaller($this->container->getEm(), $this->container->getBlobStorage(), $this->container->getImagine());

        if ($this->container->getAppManager()->hasPackage($app_package->getManifest()->getPackageName())) {
            $def = $this->container->getAppManager()->getPackage($app_package->getManifest()->getPackageName());
        } else {
            $def = null;
        }

        try {
            $def = $installer->installPackage($app_package, $def);
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);

            return $this->createApiErrorResponse('install_error', 'There was a problem installing the package: '.$e->getMessage());
        }

        return $this->createApiCreateResponse([
            'package_name' => $def->name,
        ], $this->generateUrl('api_apps_package', ['name' => $def->name]));
    }

    /**
     * @return \Application\Deskpro\App\AppManipulator
     */
    protected function getAppManipulator()
    {
        /** @var \Application\Deskpro\App\AppManipulator $appManipulator */
        $appManipulator = $this->container->getSystemService('app_manipulator');

        return $appManipulator;
    }

    public function jiraSettingsAction()
    {
        /** @var JIRA $js */
        $js   = $this->get(JIRA::NAME);
        $meta = null;
        try {
            $meta = $js->getMeta();
        } catch (\Exception $e) {
        }

        $meta = $meta ? $meta->toArray() : null;

        return $this->createApiResponse(['enabled' => $js->isEnabled(), 'meta' => $meta]);
    }

    /**
     * @param string $name
     *
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\AppStoreBundle\Domain\AppBundle
     */
    private function getAppV2ArchiveBundle($name)
    {
        $assetDir = $this->container->get('deskpro.app_env')->getAppWwwAssetDir();
        $blobPath = $assetDir.'/apps/v2/'.$name.'.zip';

        // make sure the name is slugified
        $slug    = Strings::slugifyTitle($name);
        $sysName = 'apps_v2_zip_'.$slug;

        $blob = $this->em->getRepository(Blob::class)->findOneBy([
            'sys_name' => $sysName,
        ]);

        if ($blob) {
            $appEnv   = $this->container->get('deskpro.app_env');
            $blobPath = $appEnv->getUserTmpDir().'/'.$blob->getFilename();

            $this->container->get('blob.storage')->copyBlobRecordToFile($blobPath, $blob);
        } elseif (!file_exists($blobPath)) {
            return;
        }

        /** @var BundleFileHandlingStrategyZip $bundleReader */
        $bundleReader = $this->container->get(BundleFileHandlingStrategyZip::class);

        return $bundleReader->reader($blobPath);
    }
}
