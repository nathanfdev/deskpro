import React from 'react';

import { AppsRegistry, AppsConfigBuilder } from 'DeskPRO/Bundle/AppsBundle/Modules/Config';
import { Context } from 'DeskPRO/Bundle/AppsBundle/Modules/Domain';
import { DeskproAppContainerProps, DeskproAppContainer } from 'DeskPRO/Bundle/AppsBundle/Modules/Components';
import { ManifestLoader } from 'DeskPRO/Bundle/AppsBundle/Modules/Manifest';

import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { InstallerContainer } from './Components';
import { InstallerContainerProps } from './InstallerContainerProps';

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
    const installerProps = new InstallerContainerProps({ loadAppManifest });

    return class extends React.Component {
      render() {
        installerProps.setRouteProps(this.props);
        return (
          <InstallerContainer {...installerProps.toJS()}>
            {(manifest) => {
              // const location = 'install';
              const location = 'ticket-sidebar';

              const registry = AppsRegistry.fromJS([manifest], config);
              const widgetsConfigList = registry.getWidgetConfigByTargetType(location);
              // const context = new Context({id: 5, location, type: "app", entityId: 1});
              const context = new Context({
                id:       5,
                location,
                type:     'person',
                entityId: 1,
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
