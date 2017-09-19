import React, { PropTypes } from 'react';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

import { InstallerErrorScreen, InstallerNormalScreen, InstallerLoadingScreen } from './InstallerScreens';
import { InstallerErrors } from './InstallerErrors';

const DEBUG = true;

const readAppInstallUrl = (installTargetName, manifest) => {
  const { targets } = manifest;

  let appInstallUrl = null;

  if (targets && targets instanceof Array) {
    const installUrls = targets.filter(({ target }) => target === installTargetName).map(({ url }) => url);
    appInstallUrl = installUrls.length === 1 ? installUrls.pop() : null;
  }

  return appInstallUrl;
};

const readApplicationID = ({ data: { data: { application_id: appID } } }) => appID;

export class Installer extends React.Component {
  static propTypes = {
    apiRoot:           PropTypes.string.isRequired,
    app:               PropTypes.string.isRequired,
    installTargetName: PropTypes.string.isRequired
  };

  static defaultProps = {
    installTargetName: 'install'
  };

  constructor(props)  {
    super(props);
    this.initState();
  }

  componentDidMount()  {
    const { app, installTargetName } = this.props;
    let appID = null;
    api
      .sendGet(`DP_API/apps/${app}`)
      .then((response) => {
        appID = readApplicationID(response);
        return api.sendGet(`DP_API/apps/${app}/manifest`);
      })
      .then(({ data: manifest }) => {
        const { appVersion } = manifest;
        const appInstallUrl = readAppInstallUrl(installTargetName, manifest);
        const error = appInstallUrl ? null : InstallerErrors.MISSING_TARGET;
        const screen = error ? 'error' : 'normal';

        this.setState({ error, appInstallUrl, appVersion, screen, appID });
      })
      .catch((response) => { // eslint-disable-line no-unused-expressions, no-unused-vars
        const state = { error: InstallerErrors.UNEXPECTED_ERROR };
        this.setState(state);
      })
    ;
  }

  initState()  {
    this.state = {
      appID:         1,
      appInstallUrl: null,
      appVersion:    null,
      error:         null,
      screen:        'loading'
    };
  }

  render()  {
    DEBUG && console.log('INSTALLER: api loaded ', api); // eslint-disable-line no-unused-expressions, no-unused-vars
    DEBUG && console.log('INSTALLER: props ', this.props); // eslint-disable-line no-unused-expressions, no-unused-vars

    const { screen } = this.state;

    if (screen === 'error') {
      const { error } = this.state;
      if (error) {
        return <InstallerErrorScreen error={error} />;
      }
    }

    if (screen === 'normal') {
      const { apiRoot } = this.props;
      const { appID, appVersion, appInstallUrl, error } = this.state; // eslint-disable-line no-unused-expressions, no-unused-vars
      const installUrl = `${apiRoot}/file.php/apps/${appID}/v${appVersion}/files/${appInstallUrl}`;
      return <InstallerNormalScreen installUrl={installUrl} />;
    }

    if (screen === 'loading') {
      return <InstallerLoadingScreen />;
    }

    return null;
  }
}

