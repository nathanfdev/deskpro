<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\App\Native\NativeAppsSync;
use Application\DeskPRO\App\Package\PackageInstaller;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Languages\LangPackInfo;
use Application\InstallBundle\Data\DefaultDataProcessor;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSetAsset;
use DeskPRO\Bundle\PortalBundle\Designer\PortalStylesCompiler;
use DpSys\LowError\SystemErrorHandler;
use Leafo\ScssPhp\Exception\ParserException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\RouterInterface;

class PostBuild extends AbstractBuild
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->out('Post upgrade begin');

        // Update lang titles and has_agent flags
        $langPacks = new LangPackInfo();

        foreach ($langPacks->getLangTitles(true) as $id => $title) {
            $this->out(sprintf('lang(%s).title = %s', $title, $id));
            $this->container->getDb()->executeUpdate("UPDATE languages SET title = ? WHERE sys_name = ? AND title = ''", [$title, $id]);

            $info = $langPacks->getLangInfo($id);
            $this->container->getDb()->executeUpdate('UPDATE languages SET has_user = ?, has_agent = ?, has_admin = ? WHERE sys_name = ?', [$info['has_user'], $info['has_agent'], $info['has_admin'], $id]);
        }

        // Update flags if theyre blank
        $blankFlags = $this->container->getDb()->fetchAllCol("SELECT sys_name FROM languages WHERE flag_image = ''");
        foreach ($blankFlags as $sysName) {
            if (!$langPacks->hasLang($sysName)) {
                continue;
            }

            $flag = $langPacks->getLangInfo($sysName, 'flag_image');
            if ($flag) {
                $this->out(sprintf('lang(%s).flag = %s', $flag, $sysName));
                $this->container->getDb()->executeUpdate('UPDATE languages SET flag_image = ? WHERE sys_name = ?', [$flag, $sysName]);
            }
        }

        // Auto-install any new langs
        $autoInstall = $this->container->getDb()->fetchColumn("SELECT value FROM settings WHERE name = 'core.lang_auto_install'");
        if ($autoInstall) {
            $this->out('running lang auto-install');
            $this->container->getEm()->getRepository(Language::class)->installAll($langPacks);
        }

        $this->out('invalidate lang cache');
        $this->out('invalidate lang js cache');

        #------------------------------
        # Reset opcache
        #------------------------------

        $this->out('Reset OPcache');

        $url = $this->container->getRouter()->generate('sys_serverinfo', [
            'path'  => 'opcache',
            'reset' => '1',
            'auth'  => $this->container->get('deskpro.app_env')->getServerInfoAuth('opcache'),
        ], RouterInterface::ABSOLUTE_URL);

        $ctx = stream_context_create(['http' => ['timeout' => 10, 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]]);
        @file_get_contents($url, null, $ctx);

        $this->out('Warmup OPcache');
        $url = $this->container->getRouter()->generate('sys_serverinfo', [
            'path' => 'opcache/warmup',
            'auth' => $this->container->get('deskpro.app_env')->getServerInfoAuth('opcache/warmup'),
        ], RouterInterface::ABSOLUTE_URL);

        $ctx = stream_context_create(['http' => ['timeout' => 10, 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]]);
        @file_get_contents($url, null, $ctx);

        #------------------------------
        # Data
        #------------------------------

        $this->out('Syncing default data');

        $dataProcessor = new DefaultDataProcessor($this->container);
        if ($this->logger) {
            $dataProcessor->setLogger($this->logger);
        }
        $dataProcessor->runSync();

        $this->out('.. done syncing default data');

        #------------------------------
        # Apps
        #------------------------------

        $this->out('Syncing apps');

        $appSyncer = new NativeAppsSync(
            $this->container,
            $this->container->getAppManager(),
            new PackageInstaller($this->container->getEm(), $this->container->getBlobStorage(), $this->container->getImagine()),
            $this->logger ?: null
        );

        // Dont fail the upgrade at this point
        // but log the error so we can know something went wrong with an app
        $appSyncer->setExceptionHandler(function ($e) {
            SystemErrorHandler::logException($e);
        });

        $appSyncer->runUpdates();
        $appSyncer->runSync();

        $this->out('.. done syncing apps');

        #------------------------------
        # Clear error logs
        #------------------------------

        foreach (['cli-phperr.log', 'server-phperr-web.log', 'error.log'] as $l) {
            $path = dp_get_log_dir().DIRECTORY_SEPARATOR.$l;
            if (file_exists($path)) {
                $this->out('resetting '.$l);
                @file_put_contents($path, '');
            }
        }

        #------------------------------
        # Recompile tempaltes
        #------------------------------

        $cmd = $this->container->get('deskpro.app_env')->getConsolePhpCommand('dp:utility:recompile-templates');
        $this->out("Cmd: $cmd");
        $proc = new Process(
            $cmd,
            $this->container->get('deskpro.app_env')->getAppDir()
        );
        $proc->setTimeout(900);
        $proc->run(function ($type, $dat) {
            if ($type === Process::OUT) {
                $this->out($dat);
            } else {
                $this->out('ERR: '.$dat);
            }
        });

        if (!$proc->isSuccessful()) {
            $this->out('!!! ERROR: Compiling templates returned FAILURE');
        }

        #------------------------------
        # Compile Custom Scss
        #------------------------------

        $this->out('Recompiling CSS');

        $em = $this->container->getEm();

        $styleCompiler    = $this->container->get('dp.portal.designer.portal_styles_compiler');
        $sassDocParser    = $this->container->get('dp.portal.designer.sass_doc_parser');
        $defaultVariables = $sassDocParser->getVariableValues();

        $brands = $em->getRepository(Brand::class)->findAll();

        foreach ($brands as $brand) {
            $themes = [$brand->getThemeSet(), $brand->getEditThemeSet()];
            foreach ($themes as $t) {
                if (!$t) {
                    continue;
                }

                // Just a check to see if we need to do a compile at all
                $asset = $em->getRepository(ThemeSetAsset::class)->findOneBy([
                    'name'      => 'portal.css',
                    'theme_set' => $t,
                ]);

                if ($asset) {
                    $this->out(sprintf('Recompile CSS for brand %s, theme %s', $brand->getName(), $t->getId()));
                    $vars = array_merge($defaultVariables, $t->getOption(PortalStylesCompiler::$customVarsThemeSetOption, []));

                    try {
                        $styleCompiler->recompile($vars, $t);
                    } catch (ParserException $e) {
                        SystemErrorHandler::logException($e);
                    }

                    $this->out('.. done');
                }
            }
        }

        $this->out('.. done recompiling CSS');

        $this->out('Post upgrade done');
    }
}
