import PropTypes from 'prop-types';
import React from 'react';

import { ScreenInstallerError } from './ScreenInstallerError';
import { ScreenInstallerLoading } from './ScreenInstallerLoading';
import { ScreenConfirmInstall } from './ScreenConfirmInstall';
import { InstallerErrors } from '../InstallerErrors';

export class InstallerContainer extends React.Component {
  static propTypes = {
    app:                    PropTypes.string.isRequired,
    installType:            PropTypes.string.isRequired,
    loadInstance:           PropTypes.func.isRequired,
    createInstance:         PropTypes.func.isRequired,
    loadPackage:            PropTypes.func.isRequired,
    loadInstaller:          PropTypes.func.isRequired,
    renderInstallerBundled: PropTypes.func.isRequired,
    renderInstallerDefault: PropTypes.func.isRequired
  };

  constructor(props)  {
    super(props);
    this.initState();
  }

  componentDidMount()  {
    this.loadInitialState()
      .catch(error => ({ route: 'error', error }))
      .then(state => this.setState(state))
    ;
  }

  initState()  {
    this.state = {
      error:             null,
      errorType:         null,
      route:             'loading',
      appManifest:       null,
      installerManifest: null,
      packageManifest:   null
    };
  }

  loadInitialState()  {
    const { installType } = this.props;

    if (installType === 'update') {
      return this.loadAppManifests();
    }
    if (installType === 'install') {
      return this.loadPackageManifest();
    }

    const error = new Error('unexpected install action');
    error.deskpro = { type: InstallerErrors.UNEXPECTED_INSTALL_TYPE, installType };
    return Promise.reject(error);
  }

  loadPackageManifest()  {
    const { app, loadPackage } = this.props;

    return loadPackage(app)
      .then(packageManifest => ({ route: 'confirm-install',  packageManifest }))
      .catch((error) => {
        if (typeof error === 'object') {
          error.deskpro = { type: InstallerErrors.LOAD_MANIFEST_FAIL_PACKAGE, app };
        }

        return {
          route:     'error',
          error,
          errorType: InstallerErrors.LOAD_MANIFEST_FAIL_PACKAGE
        };
      })
    ;
  }

  /**
   * @param {boolean} [createInstanceFirst]
   */
  loadAppManifests(createInstanceFirst)  {
    const { app, loadInstance, loadInstaller, createInstance } = this.props;
    const action = createInstanceFirst ? createInstance : loadInstance;

    let appManifest;
    return action(app)
      .then((manifest) => {
        appManifest = manifest;
        return loadInstaller(manifest);
      })
      .then(installerManifest => ({ route: 'settings', installerManifest, appManifest }))
      .catch((error) => {
        if (typeof error === 'object') {
          error.deskpro = { type: InstallerErrors.LOAD_MANIFEST_FAIL_APP, app, createInstanceFirst };
        }

        return { route: 'error', error, errorType: InstallerErrors.LOAD_MANIFEST_FAIL_APP };
      })
    ;
  }

  render()  {
    const { route } = this.state;

    if (route === 'error') {
      const { error } = this.state;
      if (error) {
        return <ScreenInstallerError error={error} />;
      }
    }

    if (route === 'confirm-install') {
      const { packageManifest } = this.state;
      return (<ScreenConfirmInstall
        onConfirm={() => this.loadAppManifests(true).then(state => this.setState(state))}
        packageManifest={packageManifest}
      />
      );
    }

    if (route === 'settings') {
      const { installType, renderInstallerBundled, renderInstallerDefault } = this.props;
      const { appManifest, installerManifest, packageManifest } = this.state;
      if (installerManifest) { // we have an installer
        return renderInstallerBundled({ installType, appManifest, installerManifest });
      }
      return renderInstallerDefault({ installType, appManifest, packageManifest });
    }

    if (route === 'loading') {
      return <ScreenInstallerLoading />;
    }

    const error = new Error('unknown installer route');
    return <ScreenInstallerError error={error} />;
  }
}

