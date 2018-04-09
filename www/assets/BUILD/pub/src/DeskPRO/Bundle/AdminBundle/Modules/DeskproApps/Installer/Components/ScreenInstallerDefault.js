import React from 'react';
import PropTypes from 'prop-types';
import { Progress, ProgressBar } from '@deskpro/react-components';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { AppInfo } from './AppInfo';
import { ScreenInstallerError } from './ScreenInstallerError';
import { InstallerErrors } from '../InstallerErrors';

function delay(interval, f) {
  if (window && typeof window.setTimeout === 'function') {
    window.setTimeout(f, interval);
  } else {
    f();
  }
}

export class ScreenInstallerDefault extends React.Component {
  static propTypes = {
    instanceId:        PropTypes.number.isRequired,
    packageManifest:   PropTypes.object.isRequired,
    onInstallFinished: PropTypes.func.isRequired
  };

  constructor(props)  {
    super(props);
    this.state = {
      progress: 0,
      error:    null
    };
  }

  componentDidMount()  {
    const { instanceId, onInstallFinished } = this.props;

    api.sendPut(`DP_API/apps/${instanceId}`, { is_installed: true  })
      .then(() => {
        this.setState({ progress: 100 });
        delay(500, () => onInstallFinished(instanceId));
      }).catch((error) => {
        if (typeof error === 'object') {
          error.deskpro = { type: InstallerErrors.INSTALL_DEFAULT_FAIL, instanceId };
        }

        this.setState({ error });
      });
  }

  render() {
    const { packageManifest } = this.props;

    if (this.state.error) {
      return (
        <ScreenInstallerError error={this.state.error} />
      );
    }

    return (
      <AppInfo
        iconUrl={packageManifest.icon_url}
        description={packageManifest.manifest.description}
        title={packageManifest.manifest.title}
        version={packageManifest.manifest.appVersion}
      >
        <Progress size="large" type="primary" style={{ border: '1px solid #ccc' }}>
          <ProgressBar percent={this.state.progress}>{this.state.progress} %</ProgressBar>
        </Progress>
      </AppInfo>
    );
  }
}
