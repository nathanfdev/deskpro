import React from 'react';

import { AppsConfigBuilder } from 'DeskPRO/Bundle/AppsBundle/Modules/Config';
import { ManifestLoader, ManifestParsers } from 'DeskPRO/Bundle/AppsBundle/Modules/Manifest';

import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { InstallerContainer, ScreenInstallerBundled, ScreenInstallerDefault } from './Components';

const TARGET_INSTALL = 'install';

/**
 * @param {String} app
 * @return Promise
 */
function loadInstance(app) {
  const manifestLoader = new ManifestLoader(api);
  return manifestLoader.loadApp(app);
}

/**
 * @param {String} appName
 * @return Promise
 */
function createInstance(appName) {
  return api.sendPost(`DP_API/apps/${appName}?include=app`)
    .then(response => response.data)
    .then(ManifestParsers.parseManifestResponseBody)
  ;
}

function createLoadInstaller(config) {
  const manifestLoader = new ManifestLoader(api);

  if (config.environment === 'development') {
    return manifestLoader.loadDev.bind(manifestLoader, config.endpoint);
  }

  return (manifest) => {
    if (manifest.targets.filter(({ target }) => target === TARGET_INSTALL).length) {
      return Promise.resolve(manifest);
    }
    return Promise.resolve(null);
  };
}

/**
 * @param {String} app
 * @return Promise
 */
function loadPackage(app) {
  const manifestLoader = new ManifestLoader(api);
  return manifestLoader.loadPackage(app);
}

function renderInstallerDefault(navigateToApp, { installType,  appManifest, packageManifest }) {
  return (<ScreenInstallerDefault onInstallFinished={navigateToApp} instanceId={appManifest.id} packageManifest={packageManifest} installType={installType} />);
}

function renderInstallerBundled(config, navigateToApp, { installType,  appManifest, installerManifest }) {
  return (<ScreenInstallerBundled onInstallFinished={navigateToApp} config={config} installType={installType} appManifest={appManifest} installerManifest={installerManifest} />);
}

class InstallerFactory extends React.Component {
  /**
   * @param {Window} windowObject
   * @param {function} legacyNavigate
   * @return {{}}
   */
  static routeFactory({ windowObject, legacyNavigate })  {
    const builder = new AppsConfigBuilder();
    builder.addWindowParams(windowObject);
    const config = builder.build();

    const navigateToApp = (id) => {
      if (window && window.location) {
        window.location.hash = `/apps/apps/v2/${id}`;
      }
      legacyNavigate('apps.apps.edit-v2', { instanceId: id });
    };

    return class extends React.Component {
      render() {
        const { app, installType } = this.props.params; // eslint-disable-line react/prop-types
        return (
          <InstallerContainer
            app={decodeURIComponent(app)}
            installType={installType}
            loadInstance={loadInstance}
            createInstance={createInstance}
            loadPackage={loadPackage}
            loadInstaller={createLoadInstaller(config)}
            renderInstallerBundled={props => renderInstallerBundled(config, navigateToApp, props)}
            renderInstallerDefault={props => renderInstallerDefault(navigateToApp, props)}
          />
        );
      }
    };
  }
}

export { InstallerFactory };
