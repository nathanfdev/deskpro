import { ContainerDOM } from './Services/ContainerDOM';

import ContainerMounter from './Services/ContainerMounter';
import DeskproWindowMessageBrokerAdapter from './Services/DeskproWindowMessageBrokerAdapter';
import ReduxActionDispatcher from './Services/ReduxActionDispatcher';
import { filterAppManifestsConfig, newContextsStateSelector } from './Selectors/Main';
import DeskproAppRegistry from './Domain/DeskproAppRegistry';
import { loadApps, loadDevApp } from './Actions/Actions'
import * as WidgetAPI from './WidgetAPI'
import DeskproAppStoreConfiguration from './Domain/DeskproAppStoreConfiguration';

/**
 * @param contexts
 * @param {Function} forEach
 * @return {Map}
 */
const eachTabPageFragmentContainer = (contexts, forEach) =>
{
  const mountableContexts = new Map();

  for (const context of contexts.values()) {
    if (context.has('page')) { //page fragment
      const routeUrl = context.get('page').get('routeUrl');
      const tab = DeskPRO_Window.TabBar.findTabByRouteUrl(routeUrl);
      if (tab) {
        mountableContexts.set(context.toJS(), tab.page);
      } else {
        // TODO handle closing of tabs, unmounting of components
        console.log('found a context (tab) which was closed without any cleanup actions executed afterwards. please fix this');
      }
    }
  }

  mountableContexts.forEach(forEach);
};

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

    const widgetMessageRouter = WidgetAPI.MessageGateway.messageRouter(reduxDispatcher);
    const widgetMessageBroker = WidgetAPI.MessageGateway.messageBroker(reduxDispatcher);

    const manifests = filterAppManifestsConfig(reduxStore.getState());

    const config = DeskproAppStore.configurationFromLocation(window.location);
    const appRegistry = DeskproAppRegistry.fromJS(manifests, config);

    const containerMounter = new ContainerMounter(reduxStore, reduxDispatcher, widgetMessageRouter, widgetMessageBroker, appRegistry);
    const containerDOM = ContainerDOM.fromAttributeName('data-deskproapp');

    // subscribe to redux store changes
    reduxStore.subscribe( () => {
      const contexts = newContextsStateSelector(reduxStore.getState());
      if (contexts) {
        for (const context of contexts.values()) {
          containerMounter.mountAt(context, containerDOM.findById(context.id, window.document));

          // TODO do not trigger blindly the apps sidebar
          const tab = DeskPRO_Window.TabBar.getTab(context.tabId);
          tab.page.updateAppsSidebar();
        }
      }
    });

    // const validTargets = DeskproAppStoreConfiguration.validTargets;
    // const domScanner = list => ContainerDOMScanner.fromAttributeName('data-deskproapp').filterAllByTargetTypeList(list, validTargets);
    DeskproWindowMessageBrokerAdapter.registerListener(messageBroker)(reduxDispatcher);
  }
}

export default DeskproAppStore;
