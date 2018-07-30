import React from 'react';
import PropTypes from 'prop-types';

import uuid from 'uuid';
import { AppsRegistry } from 'DeskPRO/Bundle/AppsBundle/Modules/Config';
import { DeskproAppContainer } from 'DeskPRO/Bundle/AppsBundle/Modules/Components';
import { Context } from 'DeskPRO/Bundle/AppsBundle/Modules/Domain';
import { createInterceptor } from 'DeskPRO/Bundle/AppsBundle/Modules/Services/Interceptors';
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
   * @param {string} eventName
   * @param {*} message
   * @param {Widget} widget
   * @return {{status: boolean, result: null}}
   */
  interceptDispatchIncoming = (eventName, message, widget) => {
    const status = false;
    if (eventName === INSTALL_STATUS_EVENT) {
      // the application manifest is also available if we need it
      // const { manifest } = widgetMessage.body;
      const { status: installStatus } = message;
      if (installStatus === 'success') {
        const { onInstallFinished } = this.props;
        onInstallFinished(widget.instanceId);
      }
    }

    return { status, result: null };
  };

  render() {
    const { config, installerManifest } = this.props;

    const installerConfiguration = AppsRegistry.appConfiguration(installerManifest, config);
    const widgetsConfigList = [AppsRegistry.createWidget(TARGET_INSTALL, installerConfiguration)];
    const context = this.createRuntimeContext();

    return (<DeskproAppContainer
      context={context}
      widgetsConfigList={widgetsConfigList}
      receiveMessage={createInterceptor(this.interceptDispatchIncoming, receiveMessage)}
    />);
  }
}
