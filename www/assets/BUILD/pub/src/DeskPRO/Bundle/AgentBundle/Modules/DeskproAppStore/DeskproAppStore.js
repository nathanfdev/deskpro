import ContainerMounter from './Services/ContainerMounter';
import DeskproWindowMessageBrokerAdapter from './Services/DeskproWindowMessageBrokerAdapter';
import ReduxActionDispatcher from './Services/ReduxActionDispatcher';
import { filterAppManifestsConfig, newContextsStateSelector } from './Selectors/Main';
import DeskproAppRegistry from './Domain/DeskproAppRegistry';
import { loadApps, loadDevApp } from './Actions/Actions';

import DeskproAppStoreConfiguration from './Domain/DeskproAppStoreConfiguration';
import { AppServices, mountContextInWindow } from './Services';
import { registerIncomingWidgetRequestListeners, dispatchOutgoingWidgetRequestOnIntercept } from './WidgetMessage';

class DeskproAppStore {
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
  static configurationFromLocation(locationObject)  {
    const { search } = locationObject;
    let environment = 'production';

    const queryParams = search.substring(1).split('&').map(nameAndValue => nameAndValue.split('='));
    for (const param of queryParams) {
      const [name, value] = param;
      // TODO put this together with the rest of the configuration
      if (name === 'appstore.environment' && DeskproAppStoreConfiguration.validEnvironments.indexOf(value) !== -1) {
        environment = value;
        break;
      }
    }

    const location = {
      origin:   locationObject.origin,
      host:     locationObject.host,
      hostname: locationObject.hostname,
      port:     locationObject.port,
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
  static dispatchLoadAppManifestsAction(reduxDispatch, api, config)  {
    const shouldLoadDevApp = config.environment === 'development';
    const action = shouldLoadDevApp ? loadDevApp(api, DeskproAppStoreConfiguration.devAppManifestUrl) : loadApps(api);
    reduxDispatch(action);
  }

  /**
   * Hook called before any other run activity begins
   *
   * @param {Object} reduxStore
   * @param {Window} window
   */
  static onAgentLegacyAppRun(reduxStore, window) {
    // register the global object which is referenced by legacy code
    window.DeskPRO_APPSTORE = {
      dispatchOutgoingWidgetRequestOnIntercept // TODO this needs a better name
    };
  }

  /**
   * Hook called when the agent legacy app is ready and has loaded all the assets
   *
   * @param {Object} reduxStore
   * @param {Window} window
   * @param {DpApi} api
   * @param {DeskPRO.MessageBroker} messageBroker
   */
  static onAgentLegacyAppReady(reduxStore, window, api, messageBroker)  {
    const reduxDispatcher = ReduxActionDispatcher.fromReduxStore(reduxStore, api);
    const appServices = new AppServices({ api, window });
    registerIncomingWidgetRequestListeners(appServices);
    // registerOutgoingWidgetRequestListeners(appServices);

    const manifests = filterAppManifestsConfig(reduxStore.getState());
    const config = DeskproAppStore.configurationFromLocation(window.location);

    const appRegistry = DeskproAppRegistry.fromJS(manifests, config);

    // subscribe to redux store changes
    reduxStore.subscribe(() => {
      const contexts = newContextsStateSelector(reduxStore.getState());
      if (!contexts) { return; }

      const containerMounter = new ContainerMounter(reduxStore, reduxDispatcher, appRegistry);
      /** @var {Context} context **/
      for (const context of contexts.values()) {
        mountContextInWindow(context, containerMounter, window);
      }
    });

    // listen to new pages / tabs being loaded
    DeskproWindowMessageBrokerAdapter.registerListener(messageBroker)(reduxDispatcher);

    // find already loaded contexts in opened tabs
    const tabs = window.DeskPRO_Window.TabBar.getTabs();
    const pages = tabs ? Object.keys(tabs).map(key => tabs[key].page) : [];
    if (pages.length) {
      reduxDispatcher.dispatchLoadPageFragmentApps(pages);
    }
  }
}

export default DeskproAppStore;
