import ContainerDOMScanner from './Services/ContainerDOMScanner';
import ContainerMounter from './Services/ContainerMounter';
import DeskproWindowMessageBrokerAdapter from './Services/DeskproWindowMessageBrokerAdapter';
import ReduxActionDispatcher from './Services/ReduxActionDispatcher';
import { filterAppManifestsConfig, contextsStateSelector } from './Selectors/Main';
import DeskproAppRegistry from './Domain/DeskproAppRegistry';
import { loadApps, loadDevApp } from './Actions/Actions'
import * as WidgetAPI from './WidgetAPI'
import DeskproAppStoreConfiguration from './Domain/DeskproAppStoreConfiguration';

let containerMounter;
const loaderQueue = [];

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
   * Creates a redux store subscriber that dispatches actions when some app store state properties change
   *
   * @param reduxStore
   * @param {ReduxActionDispatcher} reduxActionDispatcher
   * @return {function()}
   */
  static createReduxSubscriber(reduxStore, reduxActionDispatcher)
  {
    let contexts = contextsStateSelector(reduxStore.getState());

    return () => {
      const newContexts = contextsStateSelector(reduxStore.getState());
      if (contexts !== newContexts) {
        contexts = newContexts;
        reduxActionDispatcher.dispatchMountPageFragmentContainers();
      }
    };
  }

  /**
   * Creates a loader for app containers loaded in a page fragment context
   *
   * @param {Object} context
   * @param {DeskPRO.Agent.PageFragment.Basic} page
   * @param {Function} onSuccess
   * @param {Function} onError
   * @return {function()}
   */
  static createPageFragmentLoader(context, page, onSuccess, onError)
  {
    const validTargets = DeskproAppStoreConfiguration.validTargets;

    return () => {
      const dom = page.fragmentElement.get()[0];
      const domNodeList = ContainerDOMScanner.fromAttributeName('data-deskproapp').filterByTargetTypeList(dom, validTargets);
      containerMounter.mount(domNodeList, context);
      onSuccess();
    };
  }

  /**
   * Loads a page fragment app container asynchronously
   *
   * @param {Immutable.Map} context
   * @param {DeskPRO.Agent.PageFragment.Basic} page
   * @return {SyncPromise|Promise}
   */
  static asyncLoadPageFragment(context, page)
  {
    if (containerMounter) {
      return new Promise((resolve, reject) => {
        const onSuccess = () => { page.updateAppsSidebar(); resolve(); };
        DeskproAppStore.createPageFragmentLoader(context.toJS(), page, onSuccess, reject)();
      })
    }

    return new Promise((resolve, reject) => {
      const onSuccess = () => { page.updateAppsSidebar(); resolve(); };
      const loader = DeskproAppStore.createPageFragmentLoader(context.toJS(), page, onSuccess, reject)();
      loaderQueue.push(loader);
    });
  }

  /**
   * Initializes the components of the app store in the deskpro context
   *
   * @param {DpApi} api
   * @param {DeskPRO.MessageBroker} messageBroker
   * @param {Object} reduxStore
   * @param {DeskproAppStoreConfiguration} config
   */
  static bootstrap(api, messageBroker, reduxStore, config)
  {
    const reduxDispatcher = ReduxActionDispatcher.fromReduxStore(reduxStore, api);

    const widgetMessageRouter = WidgetAPI.MessageGateway.messageRouter(reduxDispatcher);
    const widgetMessageBroker = WidgetAPI.MessageGateway.messageBroker(reduxDispatcher);

    const manifests = filterAppManifestsConfig(reduxStore.getState());
    const appRegistry = DeskproAppRegistry.fromJS(manifests, config);
    containerMounter = new ContainerMounter(reduxStore, reduxDispatcher, widgetMessageRouter, widgetMessageBroker, appRegistry);

    const reduxSubscriber = this.createReduxSubscriber(reduxStore, reduxDispatcher);
    reduxStore.subscribe( reduxSubscriber );

    const validTargets = DeskproAppStoreConfiguration.validTargets;
    const domScanner = list => ContainerDOMScanner.fromAttributeName('data-deskproapp').filterAllByTargetTypeList(list, validTargets);
    DeskproWindowMessageBrokerAdapter.registerListener(messageBroker)(reduxDispatcher, domScanner);

    //empty the loaders queue
    while (loaderQueue.length) {
      const loader = loaderQueue.pop();
      loader();
    }

  }
}

export default DeskproAppStore;
