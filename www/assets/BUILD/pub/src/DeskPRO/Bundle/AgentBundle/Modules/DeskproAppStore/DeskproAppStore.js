import * as WidgetDOM from './WidgetDOM';

import ContainerMounter from './Services/ContainerMounter';
import DeskproWindowMessageBrokerAdapter from './Services/DeskproWindowMessageBrokerAdapter';
import ReduxActionDispatcher from './Services/ReduxActionDispatcher';
import { filterAppManifestsConfig, newContextsStateSelector } from './Selectors/Main';
import DeskproAppRegistry from './Domain/DeskproAppRegistry';
import { loadApps, loadDevApp } from './Actions/Actions'

import DeskproAppStoreConfiguration from './Domain/DeskproAppStoreConfiguration';
import {AppServices} from './Services/AppServices';
import {registerIncomingWidgetRequestListeners, registerOutgoingWidgetRequestListeners} from './WidgetMessage';


class DeskproAppStore
{
  /**
   * @param {Object} locationObject
   * @param {String} locationObject.file
   * @param {String} locationObject.hash
   * @param {String} locationObject.hostname
   * @param {String} locationObject.search
   * @param {String} locationObject.protocol
   * @param {String} locationObject.pathname
   * @param {String} locationObject.origin
   * @return {DeskproAppStoreConfiguration}
   */
  static configurationFromLocation(locationObject)
  {
    const { search } = locationObject;
    let environment = 'production';

    const queryParams = search.substring(1).split('&').map(nameAndValue => nameAndValue.split('='));
    for (const param of queryParams) {
      const [name, value] = param;
      // TODO put this together with the rest of the configuration
      if ( name === 'appstore.environment' && -1 !== DeskproAppStoreConfiguration.validEnvironments.indexOf(value)) {
        environment = value;
        break;
      }
    }

    const location = {
      origin: locationObject.origin,
      host: locationObject.host,
      hostname: locationObject.hostname,
      port: locationObject.port,
      protocol: locationObject.protocol
    };

    return new DeskproAppStoreConfiguration(environment, location);
  }

  /**
   * Dispatches the action to load the app manifests
   *
   * @param {Function} reduxDispatch
   * @param {DpApi} api
   * @param {DeskproAppStoreConfiguration} config
   */
  static dispatchLoadAppManifestsAction(reduxDispatch, api, config)
  {
    const action = config.environment === 'development' ? loadDevApp(api, DeskproAppStoreConfiguration.devAppManifestUrl) : loadApps(api);
    reduxDispatch(action);
  }

  /**
   * Initializes the components of the app store in the deskpro context
   *
   * @param {DpApi} api
   * @param {DeskPRO.MessageBroker} messageBroker
   * @param {Object} reduxStore
   * @param {Window} window
   */
  static bootstrap(api, messageBroker, reduxStore, window)
  {
    const reduxDispatcher = ReduxActionDispatcher.fromReduxStore(reduxStore, api);
    const appServices = new AppServices({ api, window });
    registerIncomingWidgetRequestListeners(appServices);
    registerOutgoingWidgetRequestListeners(appServices, messageBroker);

    const manifests = filterAppManifestsConfig(reduxStore.getState());

    const config = DeskproAppStore.configurationFromLocation(window.location);
    const appRegistry = DeskproAppRegistry.fromJS(manifests, config);

    const containerMounter = new ContainerMounter(reduxStore, reduxDispatcher, appRegistry);

    // subscribe to redux store changes
    reduxStore.subscribe( () => {
      const contexts = newContextsStateSelector(reduxStore.getState());
      if (contexts) {
        /** @var {Context} context **/
        for (const context of contexts.values()) {
          // find the dom node to mount at
          const mountAtNode = WidgetDOM.container.findContainerById(context.id, window.document);

          // TODO have the context initiate any post-mounting activities. This is quick hack-fix
          const nrOfWidgets = containerMounter.mountAt(context, mountAtNode);
          if ( nrOfWidgets && ['ticket-sidebar', 'person-sidebar', 'org-sidebar'].indexOf(context.locationId) > -1 ) {
            const tab = DeskPRO_Window.TabBar.getTab(context.tabId);
            tab.page.updateAppsSidebar();
          }
        }
      }
    });

    // const validTargets = DeskproAppStoreConfiguration.validTargets;
    // const domScanner = list => ContainerDOMScanner.fromAttributeName('data-deskproapp').filterAllByTargetTypeList(list, validTargets);
    DeskproWindowMessageBrokerAdapter.registerListener(messageBroker)(reduxDispatcher);
  }
}

export default DeskproAppStore;
