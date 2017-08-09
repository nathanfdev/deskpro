import ContainerMounter from './Services/ContainerMounter';
import DeskproWindowMessageBrokerAdapter from './Services/DeskproWindowMessageBrokerAdapter';
import { filterAppManifestsConfig, filterApiToken, newContextsStateSelector, filterAppstoreConfig } from './Selectors/Main';
import DeskproAppRegistry from './Domain/DeskproAppRegistry';
import { loadApps, loadApiToken, loadPageFragmentApps, loadAppstoreConfig } from './Actions/Actions';

import { DeskproAppStoreConfigBuilder } from './DeskproAppStoreConfigBuilder';

import { AppServices, mountContextInWindow } from './Services';
import {
  registerIncomingWidgetRequestListeners,
  registerOutgoingWidgetRequestListeners,
  dispatchOutgoingWidgetRequestOnIntercept
} from './WidgetMessage';

/**
 * Dispatches the action to load the api token
 *
 * @param {Function} reduxDispatch
 * @param {DpApi} api
 * @param {DeskproAppStoreConfiguration} config
 * @return {Promise}
 */
const dispatchLoadAppstoreConfig = (reduxDispatch, api, config)  => {
  const action = loadAppstoreConfig({ api, config });
  return reduxDispatch(action);
};

/**
 * Dispatches the action to load the api token
 *
 * @param {Function} reduxDispatch
 * @param {DpApi} api
 * @param {DeskproAppStoreConfiguration} config
 * @return {Promise}
 */
const dispatchLoadApiToken = (reduxDispatch, api, config) => {
  const action = loadApiToken({ api, config });
  return reduxDispatch(action);
};

/**
 * Dispatches the action to load the app manifests
 *
 * @param {Function} reduxDispatch
 * @param {DpApi} api
 * @param {DeskproAppStoreConfiguration} config
 * @return {Promise}
 */
const dispatchLoadAppManifestsAction = (reduxDispatch, api, config) => {
  const action = loadApps({ api, config });
  reduxDispatch(action);
};


class DeskproAppStore {
  /**
   * @param {Window} windowObject
   * @return {DeskproAppStoreConfigBuilder}
   */
  static configureWithWindowParams(windowObject)  {
    const builder = new DeskproAppStoreConfigBuilder();
    return builder.addWindowParams(windowObject);
  }

  /**
   * Bootstraps the deskpro app store
   *
   * @param {Function} reduxDispatch
   * @param {DpApi} api
   * @param {DeskproAppStoreConfiguration} config
   * @return {Promise}
   */
  static bootstrap(reduxDispatch, api, config)  {
    return Promise.all([
      dispatchLoadAppstoreConfig(reduxDispatch, api, config),
      dispatchLoadAppManifestsAction(reduxDispatch, api, config),
      dispatchLoadApiToken(reduxDispatch, api, config),
    ]);
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
   * @param {Window} windowObject
   * @param {DpApi} api
   * @param {DeskPRO.MessageBroker} messageBroker
   */
  static onAgentLegacyAppReady(reduxStore, windowObject, api, messageBroker)  {
    const state = reduxStore.getState();
    const apiToken = filterApiToken(state);
    const manifests = filterAppManifestsConfig(state);
    const config = filterAppstoreConfig(state);

    const appRegistry = DeskproAppRegistry.fromJS(manifests, config);

    const appServices = new AppServices({ api, apiToken, window: windowObject, config });
    appServices.onAppStateChanged(state);
    registerIncomingWidgetRequestListeners(appServices);
    registerOutgoingWidgetRequestListeners(appServices);

    // subscribe to redux store changes
    reduxStore.subscribe(() => {
      const newState = reduxStore.getState();

      // notify app services
      appServices.onAppStateChanged(newState);

      // mount new contexts if any
      const newContexts = newContextsStateSelector(newState);
      if (newContexts) {
        const containerMounter = new ContainerMounter(reduxStore, appRegistry);
        /** @var {Context} context **/
        for (const context of newContexts.values()) {
          mountContextInWindow(context, containerMounter, windowObject);
        }
      }
    });

    // listen to new pages / tabs being loaded
    DeskproWindowMessageBrokerAdapter.registerListener(messageBroker, config)(reduxStore.dispatch, config);

    // find already loaded contexts in opened tabs, which have been initialized
    const tabs = windowObject.DeskPRO_Window.TabBar.getTabs();
    const pages = tabs ? Object.keys(tabs).map(key => tabs[key].page).filter(page => !!page.wrapper) : [];
    if (pages.length) {
      const action = loadPageFragmentApps(pages, config, windowObject.location);
      reduxStore.dispatch(action);
    }
  }
}

export default DeskproAppStore;
