import ContainerMounter from './Services/ContainerMounter';
import DeskproWindowMessageBrokerAdapter from './Services/DeskproWindowMessageBrokerAdapter';
import { filterAppManifestsConfig, newContextsStateSelector } from './Selectors/Main';
import DeskproAppRegistry from './Domain/DeskproAppRegistry';
import { loadApps, loadPageFragmentApps } from './Actions/Actions';

import DeskproAppStoreConfiguration from './Domain/DeskproAppStoreConfiguration';
import { AppServices, mountContextInWindow } from './Services';
import {
  registerIncomingWidgetRequestListeners,
  registerOutgoingWidgetRequestListeners,
  dispatchOutgoingWidgetRequestOnIntercept
} from './WidgetMessage';

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
    const environment = 'production';
    const configParamPrefix = 'appstore.';

    const props = search.substring(1).split('&')
      .map(nameAndValue => nameAndValue.split('='))
      .filter((nameAndValue) => {
        const [name] = nameAndValue;
        return name.substr(0, configParamPrefix.length) === configParamPrefix;
      })
      .reduce((acc, nameAndValue) => {
        const [name, value] = nameAndValue;
        const key = name.substr(configParamPrefix.length);
        acc[key] = value;
        return acc;
      }, {})
    ;

    props.location = {
      origin:   locationObject.origin,
      host:     locationObject.host,
      hostname: locationObject.hostname,
      port:     locationObject.port,
      protocol: locationObject.protocol
    };

    if (!props.environment || DeskproAppStoreConfiguration.validEnvironments.indexOf(props.environment) === -1) {
      props.environment = environment;
    }

    const isDev = props.environment === 'development';
    props.endpoint = isDev ? DeskproAppStoreConfiguration.devEndpoint : props.location.origin;

    return new DeskproAppStoreConfiguration(props);
  }

  /**
   * Dispatches the action to load the app manifests
   *
   * @param {Function} reduxDispatch
   * @param {DpApi} api
   * @param {DeskproAppStoreConfiguration} config
   */
  static dispatchLoadAppManifestsAction(reduxDispatch, api, config)  {
    const action = loadApps(api, config);
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
    const appServices = new AppServices({ api, window });

    registerIncomingWidgetRequestListeners(appServices);
    registerOutgoingWidgetRequestListeners(appServices);

    const manifests = filterAppManifestsConfig(reduxStore.getState());
    const config = DeskproAppStore.configurationFromLocation(window.location);

    const appRegistry = DeskproAppRegistry.fromJS(manifests, config);

    // subscribe to redux store changes
    reduxStore.subscribe(() => {
      const contexts = newContextsStateSelector(reduxStore.getState());
      if (!contexts) { return; }

      const containerMounter = new ContainerMounter(reduxStore, appRegistry);
      /** @var {Context} context **/
      for (const context of contexts.values()) {
        mountContextInWindow(context, containerMounter, window);
      }
    });

    // listen to new pages / tabs being loaded
    DeskproWindowMessageBrokerAdapter.registerListener(messageBroker, config)(reduxStore.dispatch, config);

    // find already loaded contexts in opened tabs, which have been initialized
    const tabs = window.DeskPRO_Window.TabBar.getTabs();
    const pages = tabs ? Object.keys(tabs).map(key => tabs[key].page).filter(page => !!page.wrapper) : [];
    if (pages.length) {
      const action = loadPageFragmentApps(pages, config, window.location);
      reduxStore.dispatch(action);
    }
  }
}

export default DeskproAppStore;
