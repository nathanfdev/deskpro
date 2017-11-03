import React, { PropTypes } from 'react';

import { ScreenInstallerError } from './ScreenInstallerError';
import { ScreenInstallerLoading } from './ScreenInstallerLoading';
import { InstallerErrors } from '../InstallerErrors';

const DEBUG = true;

export class InstallerContainer extends React.Component {
  static propTypes = {
    app:                   PropTypes.string.isRequired,
    loadAppManifest:       PropTypes.func.isRequired,
    loadInstallerManifest: PropTypes.func.isRequired,
    children:              PropTypes.func.isRequired
  };

  constructor(props)  {
    super(props);
    this.initState();
  }

  componentDidMount()  {
    const { app, loadAppManifest, loadInstallerManifest } = this.props;

    let appManifest;

    loadAppManifest(app)
      .then((manifest) => {
        appManifest = manifest;
        return loadInstallerManifest(manifest);
      })
      .then(installerManifest => ({ screen: 'normal', installerManifest, appManifest }))
      .catch(err =>  // eslint-disable-line no-unused-expressions, no-unused-vars
         ({ screen: 'error', error: InstallerErrors.UNEXPECTED_ERROR }))
      .then(state => this.setState(state))
    ;
  }

  initState()  {
    this.state = {
      error:             null,
      screen:            'loading',
      appManifest:       null,
      installerManifest: null,
    };
  }

  render()  {
    DEBUG && console.log('INSTALLER: props ', this.props); // eslint-disable-line no-unused-expressions, no-unused-vars

    const { screen } = this.state;

    if (screen === 'error') {
      const { error } = this.state;
      if (error) {
        return <ScreenInstallerError error={error} />;
      }
    }

    if (screen === 'normal') {
      const { appManifest, installerManifest } = this.state; // eslint-disable-line no-unused-expressions, no-unused-vars
      return this.props.children({ appManifest, installerManifest });
    }

    if (screen === 'loading') {
      return <ScreenInstallerLoading />;
    }

    return null;
  }
}

