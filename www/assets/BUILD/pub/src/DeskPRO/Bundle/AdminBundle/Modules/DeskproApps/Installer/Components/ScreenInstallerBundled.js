import React from 'react';
import PropTypes from 'prop-types';

import uuid from 'uuid';
import { AppsRegistry } from 'DeskPRO/Bundle/AppsBundle/Modules/Config';
import { DeskproAppContainerProps, DeskproAppContainer } from 'DeskPRO/Bundle/AppsBundle/Modules/Components';
import { Context } from 'DeskPRO/Bundle/AppsBundle/Modules/Domain';

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
   * Installer widget message interceptor for the INSTALL_STATUS_EVENT
   *
   * @param {Widget} widget
   * @param {String} eventName
   * @param {WidgetRequest|WidgetResponse} widgetMessage
   * @param {function} next
   */
  dispatchIncomingWidgetMessage = (eventName, widgetMessage, widget, next)  => {
    const { onInstallFinished } = this.props;

    if (eventName === INSTALL_STATUS_EVENT) {
      // the application manifest is also available if we need it
      // const { manifest } = widgetMessage.body;
      const { status } = widgetMessage.body;
      if (status === 'success') {
        onInstallFinished(widget.instanceId);
        return;
      }
    }
    next(eventName, widgetMessage, widget);
  };

  render() {
    const { config, installerManifest } = this.props;

    const installerConfiguration = AppsRegistry.appConfiguration(installerManifest, config);
    const widgetsConfigList = [AppsRegistry.createWidget(TARGET_INSTALL, installerConfiguration)];

    const context = this.createRuntimeContext();

    const props = DeskproAppContainerProps.create({ context, widgetsConfigList, dispatchIncomingWidgetMessage: this.dispatchIncomingWidgetMessage });
    return (<DeskproAppContainer {...props} />);
  }
}
