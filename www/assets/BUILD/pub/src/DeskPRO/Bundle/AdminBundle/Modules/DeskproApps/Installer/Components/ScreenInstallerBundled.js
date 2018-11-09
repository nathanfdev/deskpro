import React from 'react';
import PropTypes from 'prop-types';

import uuid from 'uuid';
import { apps } from 'DeskPRO/Bundle/AppsBundle/Modules/Config';
import { DeskproAppContainer } from 'DeskPRO/Bundle/AppsBundle/Modules/Components';
import { Context } from 'DeskPRO/Bundle/AppsBundle/Modules/Domain';
import { createInterceptor } from 'DeskPRO/Bundle/AppsBundle/Modules/Services/interceptors';
import { receiveMessage } from 'DeskPRO/Bundle/AppsBundle/Modules/WidgetMessage';

const TARGET_INSTALL = 'install';
const INSTALL_STATUS_EVENT = 'install.status';

export class ScreenInstallerBundled extends React.Component {
  static propTypes = {
    onInstallFinished: PropTypes.func.isRequired,
    config:            PropTypes.object.isRequired,
    installType:       PropTypes.string.isRequired,
    appManifest:       PropTypes.object.isRequired,
    installerManifest: PropTypes.object.isRequired
  };

  /**
   * @returns {Context}
   */
  createRuntimeContext()  {
    const { installType, appManifest } = this.props;

    return new Context({
      id:              uuid.v4(),
      target:          TARGET_INSTALL,
      type:            'app',
      entityId:        appManifest.application_id,
      installType,
      onInstallStatus: {
        event:          INSTALL_STATUS_EVENT,
        invocationType: 'event.invocation_fireandforget'
      }
    });
  }

  /**
   * @return {{status: boolean, result: null}}
   * @param {Widget} widget
   * @param {{ data:Object }} ev
   */
  interceptDispatchIncoming = (widget, ev) => {
    const status = false;

    if (ev.data.eventName === INSTALL_STATUS_EVENT) {
      // the application manifest is also available if we need it
      // const { manifest } = widgetMessage.body;
      const { status: installStatus } = ev.data.body;
      if (installStatus === 'success') {
        const { onInstallFinished } = this.props;
        onInstallFinished(widget.instanceId);
      }
    }

    return { status, result: null };
  };

  render() {
    const { config, installerManifest } = this.props;
    const widget = apps.createWidget({ manifest: installerManifest, settings: {}, bundleUpdatedAt: 0 }, config, TARGET_INSTALL);
    const context = this.createRuntimeContext();

    return (<DeskproAppContainer
      context={context}
      widgetsConfigList={[widget]}
      receiveMessage={createInterceptor(this.interceptDispatchIncoming, receiveMessage)}
    />);
  }
}
