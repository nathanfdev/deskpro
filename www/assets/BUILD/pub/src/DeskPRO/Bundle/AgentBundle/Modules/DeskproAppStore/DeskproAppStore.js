import ContainerDOMScanner from './Services/ContainerDOMScanner';
import ContainerMounter from './Services/ContainerMounter';
import AppMessageGateway from './Services/AppMessageGateway';
import DeskproWindowMessageBrokerAdapter from './Services/DeskproWindowMessageBrokerAdapter';
import ReduxActionDispatcher from './Services/ReduxActionDispatcher';
import { filterAppConfig, contextsStateSelector } from './Selectors/Main';
import DeskproAppRegistry from './Domain/DeskproAppRegistry';
import { loadApps } from './Actions/Actions'

let containerMounter;
const loaderQueue = [];

class DeskproAppStore
{
  static get validTargets()
  {
    return ['top-bar', 'ticket-sidebar'];
  }

  /**
   * Dispatches the action to load the apps configuration
   *
   * @param {Function} reduxDispatch
   * @param {DpApi} api
   */
  static dispatchLoadApps(reduxDispatch, api)
  {
    const action = loadApps(api);
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
    const { validTargets } = this;

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
   */
  static bootstrap(api, messageBroker, reduxStore)
  {
    const reduxDispatcher = ReduxActionDispatcher.fromReduxStore(reduxStore, api);

    const widgetMessageRouter = AppMessageGateway.messageRouter(reduxDispatcher);
    const widgetMessageBroker = AppMessageGateway.messageBroker(reduxDispatcher);

    const appConfig = filterAppConfig(reduxStore.getState());
    const appRegistry = DeskproAppRegistry.fromJS(appConfig);
    containerMounter = new ContainerMounter(reduxStore, reduxDispatcher, widgetMessageRouter, widgetMessageBroker, appRegistry);

    const reduxSubscriber = this.createReduxSubscriber(reduxStore, reduxDispatcher);
    reduxStore.subscribe( reduxSubscriber );

    const domScanner = list => ContainerDOMScanner.fromAttributeName('data-deskproapp').filterAllByTargetTypeList(list, this.validTargets);
    DeskproWindowMessageBrokerAdapter.registerListener(messageBroker)(reduxDispatcher, domScanner);

    //empty the loaders queue
    while (loaderQueue.length) {
      const loader = loaderQueue.pop();
      loader();
    }

  }
}

export default DeskproAppStore;
