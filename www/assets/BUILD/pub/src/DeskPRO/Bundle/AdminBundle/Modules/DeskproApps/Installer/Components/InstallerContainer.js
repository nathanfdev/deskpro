import React, { PropTypes } from 'react';

import { ScreenInstallerError } from './ScreenInstallerError';
import { ScreenInstallerLoading } from './ScreenInstallerLoading';
import { InstallerErrors } from '../InstallerErrors';

const DEBUG = true;

export class InstallerContainer extends React.Component {
  static propTypes = {
    app:             PropTypes.string.isRequired,
    loadAppManifest: PropTypes.func.isRequired,
    children:        PropTypes.func.isRequired
  };

  constructor(props)  {
    super(props);
    this.initState();
  }

  componentDidMount()  {
    const { app, loadAppManifest } = this.props;

    loadAppManifest(app).then((manifest) => {
      this.setState({ manifest, screen: 'normal' });
    }).catch((err) => { // eslint-disable-line no-unused-expressions, no-unused-vars
      const state = { error: InstallerErrors.UNEXPECTED_ERROR, screen: 'error' };
      this.setState(state);
    });
  }

  initState()  {
    this.state = {
      error:    null,
      screen:   'loading',
      manifest: null
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
      const { manifest } = this.state; // eslint-disable-line no-unused-expressions, no-unused-vars
      return this.props.children(manifest);
    }

    if (screen === 'loading') {
      return <ScreenInstallerLoading />;
    }

    return null;
  }
}

