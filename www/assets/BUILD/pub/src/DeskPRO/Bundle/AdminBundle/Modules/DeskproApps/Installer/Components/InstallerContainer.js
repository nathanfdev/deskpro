import PropTypes from 'prop-types';
import React from 'react';

import { ScreenInstallerError } from './ScreenInstallerError';
import { ScreenInstallerLoading } from './ScreenInstallerLoading';
import { ScreenConfirmInstall } from './ScreenConfirmInstall';
import { InstallerErrors } from '../InstallerErrors';

const DEBUG = true;

export class InstallerContainer extends React.Component {
  static propTypes = {
    app:                   PropTypes.string.isRequired,
    installAction:         PropTypes.string.isRequired,
    loadAppManifest:       PropTypes.func.isRequired,
    loadPackageManifest:   PropTypes.func.isRequired,
    loadInstallerManifest: PropTypes.func.isRequired,
    children:              PropTypes.func.isRequired
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
    const { installAction } = this.props;

    if (installAction === 'update') {
      return this.loadAppManifests();
    }
    if (installAction === 'install') {
      return this.loadPackageManifest();
    }

    return Promise.resolve({ screen: 'error', error: InstallerErrors.UNEXPECTED_INSTALL_ACTION });
  }

  loadPackageManifest()  {
    const { app, loadPackageManifest } = this.props;

    loadPackageManifest(app)
      .then(packageManifest => ({ route: 'confirm-install',  packageManifest }))
      .catch(err =>  // eslint-disable-line no-unused-expressions, no-unused-vars
         ({ route: 'error', error: InstallerErrors.UNEXPECTED_ERROR }))
    ;
  }

  loadAppManifestsBinding = () => this.loadAppManifests();

  loadAppManifests()  {
    const { app, installAction, loadAppManifest, loadInstallerManifest } = this.props;

    let appManifest;

    return loadAppManifest(installAction, app)
      .then((manifest) => {
        appManifest = manifest;
        return loadInstallerManifest(manifest);
      })
      .then(installerManifest => ({ route: 'settings', installerManifest, appManifest }))
      .catch(err =>  // eslint-disable-line no-unused-expressions, no-unused-vars
         ({ route: 'error', error: InstallerErrors.UNEXPECTED_ERROR }))
    ;
  }

  render()  {
    DEBUG && console.log('INSTALLER: props ', this.props); // eslint-disable-line no-unused-expressions, no-unused-vars

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
        iconUrl={packageManifest.icon_url}
        description={packageManifest.manifest.description}
        title={packageManifest.manifest.title}
        version={packageManifest.manifest.appVersion}
        onInstall={this.loadAppManifestsBinding}
      />);
    }

    if (route === 'settings') {
      const { appManifest, installerManifest } = this.state; // eslint-disable-line no-unused-expressions, no-unused-vars
      return this.props.children({ appManifest, installerManifest });
    }

    if (route === 'loading') {
      return <ScreenInstallerLoading />;
    }

    return null;
  }
}

