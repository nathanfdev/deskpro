import React from 'react';
import uuid from 'node-uuid';

import { AppsRegistry, AppsConfigBuilder } from 'DeskPRO/Bundle/AppsBundle/Modules/Config';
import { Context } from 'DeskPRO/Bundle/AppsBundle/Modules/Domain';
import { DeskproAppContainerProps, DeskproAppContainer } from 'DeskPRO/Bundle/AppsBundle/Modules/Components';
import { ManifestLoader } from 'DeskPRO/Bundle/AppsBundle/Modules/Manifest';

import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { InstallerContainer } from './Components';
import { InstallerContainerProps } from './InstallerContainerProps';


const INSTALLER_TARGET = 'install';

class InstallerFactory extends React.Component {
  /**
   * @param {Window} windowObject
   * @return {{}}
   */
  static routeFactory(windowObject)  {
    const builder = new AppsConfigBuilder();
    builder.addWindowParams(windowObject);
    const config = builder.build();

    const manifestLoader = new ManifestLoader(api);
    const loadAppManifest = manifestLoader.loadApp.bind(manifestLoader);
    const containerProps = new InstallerContainerProps({ loadAppManifest });

    return class extends React.Component {
      render() {
        containerProps.setRouteProps(this.props);
        return (
          <InstallerContainer {...containerProps.toJS()}>
            {(manifest) => {
              const appConfiguration = AppsRegistry.appConfiguration(manifest, config);
              const widgetsConfigList = [AppsRegistry.createWidget(INSTALLER_TARGET, appConfiguration)];

              const context = new Context({
                id:       uuid.v4(),
                INSTALLER_TARGET,
                type:     'app',
                entityId: appConfiguration.applicationId,
                manifest
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
