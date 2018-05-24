import * as postRobot from 'post-robot';

import { AppsRegistry, AppsConfigBuilder } from 'DeskPRO/Bundle/AppsBundle/Modules/Config';
import { AppServices, mountContextInWindow, unmountContextInWindow, ContainerMounter } from 'DeskPRO/Bundle/AppsBundle/Modules/Services';
import {
  registerIncomingWidgetRequestListeners,
  registerOutgoingWidgetRequestListeners,
  dispatchOutgoingWidgetRequestOnIntercept,
  dispatchOutgoingWidgetMessage
} from 'DeskPRO/Bundle/AppsBundle/Modules/WidgetMessage';


import { DeskproWindowMessageBrokerAdapter } from './Services/DeskproWindowMessageBrokerAdapter';
import { filterAppManifestsConfig, filterApiToken, changedContextsSelector, filterAppstoreConfig } from './Selectors/Main';
import { loadApps, loadApiToken, loadContextsFromPageFragments, loadAppstoreConfig } from './Actions/Actions';

/**
 * Dispatches the action to load the api token
 *
 * @param {Function} reduxDispatch
 * @param {DpApi} api
 * @param {AppsConfig} config
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
 * @param {AppsConfig} config
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
 * @param {AppsConfig} config
 * @return {Promise}
 */
const dispatchLoadAppManifestsAction = (reduxDispatch, api, config) => {
  const action = loadApps({ api, config });
  reduxDispatch(action);
};


class DeskproAppStore {
  /**
   * @param {Window} windowObject
   * @return {AppsConfigBuilder}
   */
  static configureWithWindowParams(windowObject)  {
    const builder = new AppsConfigBuilder();
    return builder.addWindowParams(windowObject);
  }

  /**
   * Bootstraps the deskpro app store
   *
   * @param {Function} reduxDispatch
   * @param {DpApi} api
   * @param {AppsConfig} config
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
      interceptEvent: dispatchOutgoingWidgetRequestOnIntercept, // TODO this needs a better name
      dispatchEvent:  (eventName, message) => dispatchOutgoingWidgetMessage(eventName, message)
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
    const config = filterAppstoreConfig(state);

    if (config.environment === 'production') {
      postRobot.CONFIG.LOG_LEVEL = 'error';
    }

    const appServices = new AppServices({ api, apiToken, window: windowObject, config });
    appServices.onAppStateChanged(state);
    registerIncomingWidgetRequestListeners(appServices);
    registerOutgoingWidgetRequestListeners(appServices);

    // subscribe to redux store changes
    // TODO -- this is awful. needs to be changed to reducers so it doesnt run on every single action!
    reduxStore.subscribe(() => {
      const newState = reduxStore.getState();

      if (!newState.DeskproApps.Main.get('apps')) {
        return;
      }

      // notify app services
      appServices.onAppStateChanged(newState);

      // mount new contexts if any
      const changedContexts = changedContextsSelector(newState);

      const manifests = filterAppManifestsConfig(newState);
      const appRegistry = AppsRegistry.fromJS(manifests, config);
      const containerMounter = new ContainerMounter(reduxStore, appRegistry);

      /** @var {Context} context **/
      for (const context of changedContexts.deleted) {
        unmountContextInWindow(context, containerMounter, windowObject);
      }

      /** @var {Context} context **/
      for (const context of changedContexts.added) {
        mountContextInWindow(context, containerMounter, windowObject);
      }
    });

    // listen to new pages / tabs being loaded
    DeskproWindowMessageBrokerAdapter.registerListener(messageBroker, config)(reduxStore.dispatch, config);

    // find already loaded contexts in opened tabs, which have been initialized
    const tabs = windowObject.DeskPRO_Window.TabBar.getTabs();
    const pages = tabs ? Object.keys(tabs).map(key => tabs[key].page).filter(page => !!page.wrapper) : [];
    if (pages.length) {
      const action = loadContextsFromPageFragments(pages, config, windowObject.location);
      reduxStore.dispatch(action);
    }
  }
}

export default DeskproAppStore;
