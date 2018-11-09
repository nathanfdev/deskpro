import * as postRobot from 'post-robot';

import { AppsConfigBuilder, apps } from 'DeskPRO/Bundle/AppsBundle/Modules/Config';
import { AppServices, contexts } from 'DeskPRO/Bundle/AppsBundle/Modules/Services';
import {
  registerIncomingRequestListeners,
  bindIncomingMessageHandlers,
  emitAsync
} from 'DeskPRO/Bundle/AppsBundle/Modules/WidgetMessage';


import { DeskproWindowMessageBrokerAdapter } from './Services/DeskproWindowMessageBrokerAdapter';
import { filterAppManifestsConfig, filterApiToken, changedContextsSelector } from './Selectors/Main';
import { loadApps, loadApiToken, loadContextsFromPageFragments } from './Actions/Actions';


/**
 * @type {AppsConfig}
 */
let appsConfig = null;

class DeskproAppStore {
  /**
   * @param {AppsConfig} config
   */
  static setConfig(config)  {
    appsConfig = config;
  }

  /**
   * @return {AppsConfig}
   */
  static getConfig()  {
    return appsConfig;
  }

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
   * @return {Promise}
   */
  static bootstrap(reduxDispatch, api)  {
    return Promise.all([
      reduxDispatch(loadApps({ api, config: appsConfig })), // dispatch the action to load the api token
      reduxDispatch(loadApiToken({ api, config: appsConfig })), // dispatch the action to load the api token
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
      emitAsync
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

    if (appsConfig.environment === 'production') {
      postRobot.CONFIG.LOG_LEVEL = 'error';
    }

    const appServices = new AppServices({ api, apiToken, window: windowObject, config: appsConfig });
    appServices.onAppStateChanged(state);
    registerIncomingRequestListeners(bindIncomingMessageHandlers(appServices));

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
      const widgetsProvider = apps.createWidgetProvider(manifests, appsConfig);

      /** @var {Context} context **/
      for (const context of changedContexts.deleted) {
        contexts.unmountContextInWindow(context, windowObject);
      }

      /** @var {Context} context **/
      for (const context of changedContexts.available) {
        const configuration = contexts.readContextConfiguration(context, windowObject);

        contexts.mountContextStrategy(context, windowObject)({
          store:             reduxStore,
          widgetsConfigList: widgetsProvider(configuration.targetType),
          context,
          config:            appsConfig
        });
      }
    });

    // listen to new pages / tabs being loaded
    DeskproWindowMessageBrokerAdapter.registerListener(messageBroker, appsConfig)(reduxStore.dispatch, appsConfig);

    // find already loaded contexts in opened tabs, which have been initialized
    const tabs = windowObject.DeskPRO_Window.TabBar.getTabs();
    const pages = tabs ? Object.keys(tabs).map(key => tabs[key].page).filter(page => !!page.wrapper) : [];
    if (pages.length) {
      const action = loadContextsFromPageFragments(pages, appsConfig, windowObject.location);
      reduxStore.dispatch(action);
    }
  }
}

export default DeskproAppStore;
