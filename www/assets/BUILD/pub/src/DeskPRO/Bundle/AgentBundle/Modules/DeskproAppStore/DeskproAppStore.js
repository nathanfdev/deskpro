import ContainerDOMNodeSelector from './Services/ContainerDOMNodeSelector';
import DeskproAppContainerLoader from './Services/DeskproAppContainerLoader';
import DeskproWindowMessageBrokerAdapter from './Services/DeskproWindowMessageBrokerAdapter';
import ReduxActionDispatcher from './Services/ReduxActionDispatcher';
import EventBus from './Services/EventBus';
import { filterAppConfig } from './Selectors/Main';
import DeskproAppRegistry from './Domain/DeskproAppRegistry';


import { DESKPRO_APPSTORE_APPCONTEXT_CREATED, loadApps } from './Actions/Actions'

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
   * Initializes the components of the app store in the deskpro context
   *
   * @param {DpApi} api
   * @param {DeskPRO.MessageBroker} messageBroker
   * @param {Object} reduxStore
   */
  static bootstrap(api, messageBroker, reduxStore)
  {
    const dispatch = action => reduxStore.dispatch(action);

    // this event bus should be replace with the redux mechanism
    const eventBus = new EventBus();
    const reduxDispatcher = new ReduxActionDispatcher(api, eventBus, dispatch);

    const appConfig = filterAppConfig(reduxStore.getState());
    const appRegistry = DeskproAppRegistry.fromJS(appConfig);

    const containerPropsFactory = (targetType, context) => {
      return {context: context, dispatcher: reduxDispatcher, targetType, widgets: appRegistry.getWidgetConfigByTargetType(targetType)}
    };
    const containerDOMSelector = new ContainerDOMNodeSelector('data-deskproapp');

    //register a listener for any loaded content so we can instantiate any apps
    const containerLoader = new DeskproAppContainerLoader(containerDOMSelector, this.validTargets, containerPropsFactory);

    eventBus.addEventListener(
      DESKPRO_APPSTORE_APPCONTEXT_CREATED
      , (context, domNodeList) => {
          containerLoader.load(domNodeList, context, reduxStore)
        }
      );

    DeskproWindowMessageBrokerAdapter.registerListener(messageBroker)(reduxStore, containerLoader, eventBus);
  }
}

export default DeskproAppStore;
