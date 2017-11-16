import React from 'react';
import uuid from 'uuid';

import { AppsRegistry, AppsConfigBuilder } from 'DeskPRO/Bundle/AppsBundle/Modules/Config';
import { Context } from 'DeskPRO/Bundle/AppsBundle/Modules/Domain';
import { DeskproAppContainerProps, DeskproAppContainer } from 'DeskPRO/Bundle/AppsBundle/Modules/Components';
import { ManifestLoader, ManifestParsers } from 'DeskPRO/Bundle/AppsBundle/Modules/Manifest';

import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { InstallerContainer } from './Components';
import { InstallerContainerProps } from './InstallerContainerProps';

const TARGET_INSTALL = 'install';
const INSTALL_STATUS_EVENT = 'install.status';

class InstallerFactory extends React.Component {
  /**
   * @param {Window} windowObject
   * @param {function} legacyNavigate
   * @return {{}}
   */
  static routeFactory({ windowObject, legacyNavigate })  {
    const builder = new AppsConfigBuilder();
    builder.addWindowParams(windowObject);
    const config = builder.build();

    const manifestLoader = new ManifestLoader(api);

    const containerProps = new InstallerContainerProps({
      loadAppManifest: (action, app) => {
        if (action === 'update') {
          return manifestLoader.loadApp(app);
        }

        if (action === 'install') {
          // double encoded as symfony decodes the uri before matching the routes so forward slashes which are part of the
          // name will influence the matching algorithm
          const encodedAppName = encodeURIComponent(encodeURIComponent(app));
          return api.sendPost(`DP_API/apps/${encodedAppName}?include=app`)
            .then(response => response.data)
            .then(ManifestParsers.parseManifestResponseBody)
          ;
        }

        throw new Error('unknown installer action');
      },
      loadPackageManifest: manifestLoader.loadPackage.bind(manifestLoader)
    });


    if (config.environment === 'development') {
      containerProps.loadInstallerManifest = manifestLoader.loadDev.bind(manifestLoader, config.endpoint);
    } else {
      containerProps.loadInstallerManifest = (manifest) => {
        if (manifest.targets.filter(({ target }) => target === TARGET_INSTALL).length) {
          return manifest;
        }
        return null;
      };
    }

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
          legacyNavigate('apps.apps.instance_v2', { id: `v2_${widget.instanceId}` });
          return;
        }
      }
      next(eventName, widgetMessage, widget);
    };

    return class extends React.Component {
      render() {
        containerProps.setRouteProps(this.props);
        return (
          <InstallerContainer {...containerProps.toJS()}>
            {({ appManifest, installerManifest }) => {
              if (!installerManifest) {
                api.sendPut(`DP_API/apps/app:${appManifest.application_id}`, { is_installed: true  })
                  .then(() => {
                    legacyNavigate('apps.apps.instance_v2', { id: `v2_${appManifest.id}` });
                  });
                return null;
              }

              const installerConfiguration = AppsRegistry.appConfiguration(installerManifest, config);
              const widgetsConfigList = [AppsRegistry.createWidget(TARGET_INSTALL, installerConfiguration)];

              const context = new Context({
                id:              uuid.v4(),
                target:          TARGET_INSTALL,
                type:            'app',
                entityId:        appManifest.application_id,
                onInstallStatus: {
                  event:          INSTALL_STATUS_EVENT,
                  invocationType: 'event.invocation_fireandforget'
                }
              });

              const props = DeskproAppContainerProps.create({ context, widgetsConfigList, dispatchIncomingWidgetMessage });
              return (<DeskproAppContainer {...props} />);
            }}
          </InstallerContainer>);
      }
    };
  }
}

export { InstallerFactory };
