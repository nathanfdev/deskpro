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
    this.loadInitialState().then(state => this.setState(state));
  }

  initState()  {
    this.state = {
      error:             null,
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

    return Promise.resolve({ screen: 'error', error: InstallerErrors.UNEXPECTED_INSTALL_ACTION });
  }

  loadPackageManifest()  {
    const { app, loadPackage } = this.props;

    return loadPackage(app)
      .then(packageManifest => ({ route: 'confirm-install',  packageManifest }))
      .catch(err =>  // eslint-disable-line no-unused-expressions, no-unused-vars
           ({ route: 'error', error: InstallerErrors.UNEXPECTED_ERROR }))
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
      .catch(err =>  // eslint-disable-line no-unused-expressions, no-unused-vars
         ({ route: 'error', error: InstallerErrors.UNEXPECTED_ERROR }))
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

    return null;
  }
}

