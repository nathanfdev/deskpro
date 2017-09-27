import React from 'react';
import uuid from 'node-uuid';

import { AppsRegistry, AppsConfigBuilder } from 'DeskPRO/Bundle/AppsBundle/Modules/Config';
import { Context } from 'DeskPRO/Bundle/AppsBundle/Modules/Domain';
import { DeskproAppContainerProps, DeskproAppContainer } from 'DeskPRO/Bundle/AppsBundle/Modules/Components';
import { ManifestLoader } from 'DeskPRO/Bundle/AppsBundle/Modules/Manifest';

import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { InstallerContainer } from './Components';
import { InstallerContainerProps } from './InstallerContainerProps';


const TARGET_INSTALL = 'install';

class InstallerFactory extends React.Component {
  /**
   * @param {Window} windowObject
   * @return {{}}
   */
  static routeFactory(windowObject)  {
    const builder = new AppsConfigBuilder();
    builder.addWindowParams(windowObject);
    const config = builder.build();

    const containerProps = new InstallerContainerProps({});
    const manifestLoader = new ManifestLoader(api);
    containerProps.loadAppManifest = manifestLoader.loadApp.bind(manifestLoader);

    if (config.environment === 'development') {
      containerProps.loadInstallerManifest = manifestLoader.loadDev.bind(manifestLoader, config.endpoint);
    } else {
      containerProps.loadInstallerManifest = manifest => manifest;
    }

    return class extends React.Component {
      render() {
        containerProps.setRouteProps(this.props);
        return (
          <InstallerContainer {...containerProps.toJS()}>
            {({ appManifest, installerManifest }) => {
              const installerConfiguration = AppsRegistry.appConfiguration(installerManifest, config);
              const widgetsConfigList = [AppsRegistry.createWidget(TARGET_INSTALL, installerConfiguration)];

              const context = new Context({
                id:       uuid.v4(),
                target:   TARGET_INSTALL,
                type:     'app',
                entityId: appManifest.application_id
              });
              const props = DeskproAppContainerProps.create({ context, widgetsConfigList });

              return (<DeskproAppContainer {...props} />);
            }}
          </InstallerContainer>);
      }
    };
  }
}

export { InstallerFactory };
