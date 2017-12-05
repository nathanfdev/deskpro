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

  render() {
    const { config, installType, appManifest, installerManifest } = this.props;

    const installerConfiguration = AppsRegistry.appConfiguration(installerManifest, config);
    const widgetsConfigList = [AppsRegistry.createWidget(TARGET_INSTALL, installerConfiguration)];

    const context = new Context({
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

    const { onInstallFinished } = this.props;

    /**
     * @param {Widget} widget
     * @param {String} eventName
     * @param {WidgetRequest|WidgetResponse} widgetMessage
     * @param {function} next
     */
    const dispatchIncomingWidgetMessage = (eventName, widgetMessage, widget, next)  => {
      if (eventName === INSTALL_STATUS_EVENT) {
        const { manifest, status } = widgetMessage.body; // eslint-disable-line no-unused-expressions, no-unused-vars
        if (status === 'success') {
          onInstallFinished(widget.instanceId);
          return;
        }
      }
      next(eventName, widgetMessage, widget);
    };

    const props = DeskproAppContainerProps.create({ context, widgetsConfigList, dispatchIncomingWidgetMessage });
    return (<DeskproAppContainer {...props} />);
  }
}
