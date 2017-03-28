import postRobot from 'post-robot/src';
import * as AppMessages from './AppMessages';

const requestHandlers = {};

/**
 * @param {String} eventName
 * @param {String} id
 * @param {Function} requestHandler
 */
function registerRequestHandler(eventName, id, requestHandler)
{
  const handlers = requestHandlers[eventName];
  if (handlers) {
    handlers[id] = requestHandler;
    return;
  }

  requestHandlers[ eventName ] =  { [id] : requestHandler } ;
}

function onWidgetMessage (eventName, message)
{
  const handlers = requestHandlers[eventName];
  if (! handlers) {
    console.log(`no handlers found for event ${eventName}`, message);
    return;
  }

  const {widgetId, args} = message;
  const handler = handlers[widgetId];

  if (! handler) { //todo should log this, something strange is happening
    console.log(`no handlers found for widget id ${widgetId}`, eventName, message);
    return;
  }

  if ( args.length ) {
    const [ message ] = args;
    handler(widgetId, message);
  } else {
    handler(widgetId);
  }
}

class MessageChannel
{
  /**
   * @param {String} eventName
   * @param {WidgetConfiguration} widget
   * @param {Function} replyHandler
   * @param {Function} defaultRequestHandler
   */
  constructor(eventName, widget, replyHandler, defaultRequestHandler) {
    this.eventName = eventName;
    this.widget = widget;
    this.replyHandler = replyHandler;
    this.defaultRequestHandler = defaultRequestHandler;
  }

  bindWithHandler = (widgetWindow, id, requestHandler) => {

    const { widget, replyHandler, eventName } = this;
    const reply = message => replyHandler(widget, widgetWindow, message);
    const dispatcher = (widgetId, message) => {
      requestHandler(widget, message, reply)
    };

    registerRequestHandler(eventName, id, dispatcher)
  };

  bind = (widgetWindow, id) => {

    const { defaultRequestHandler } = this;
    this.bindWithHandler(widgetWindow, id, defaultRequestHandler);
  }
}

class MessageBroker
{
  /**
   * @param {AppMessageGateway} gateway
   */
  constructor(gateway)
  {
    this.gateway = gateway;
  }

  bindWidget = (widget, widgetWindow, subscriptionId, subscribeToEventsList) => {

    const validSubscriptions = subscribeToEventsList.filter(subscription => this.isValidMessageSubscription(subscription));
    if (validSubscriptions.length === 0) {
      return false;
    }

    //bind to window message channels
    const { gateway } = this;
    validSubscriptions.forEach(subscription => {
      const eventName = typeof subscription == 'string' ? subscription : subscription.eventName;

      if (typeof subscription == 'string') {
        gateway.messageChannelForEvent(eventName, widget).bind(widgetWindow, subscriptionId);
      } else {
        gateway.messageChannelForEvent(eventName, widget).bindWithHandler(
          widgetWindow
          , subscriptionId
          , subscription.requestHandler
        );
      }
    });

  };

  isValidMessageSubscription = (subscription) => {

    let valid = typeof subscription == 'string' && AppMessages.isEventName(subscription);
    if (valid) {
      return valid;
    }

    valid = typeof subscription == 'object'
      && subscription.hasOwnProperty('eventName')
      && AppMessages.isEventName(subscription.eventName)
      && subscription.hasOwnProperty('requestHandler')
      && typeof subscription.requestHandler == 'function'
    ;

    return valid;
  }
}

class AppMessageGateway
{
  static MessageChannel = MessageChannel;

  static MessageBroker = MessageBroker;

  /**
   * @param {ReduxActionDispatcher} reduxDispatcher
   * @return {Function}
   */
  static messageRouter(reduxDispatcher) {
    return onWidgetMessage;
  }

  /**
   * @param {ReduxActionDispatcher} reduxDispatcher
   */
  static messageBroker(reduxDispatcher)
  {
    const gateway = new AppMessageGateway(reduxDispatcher);
    const broker = new AppMessageGateway.MessageBroker(gateway);

    return broker.bindWidget.bind(broker);
  };

  /**
   * @param {ReduxActionDispatcher} reduxDispatcher
   */
  constructor (reduxDispatcher)
  {
    this.appstoreDispatcher = reduxDispatcher;
  }

  /**
   * @param eventName
   * @return {MessageChannel}
   */
  messageChannelForEvent = (eventName, widget) =>
  {
    if (! AppMessages.isEventName(eventName)) {
      throw new Error(`${eventName} is not a known event name`);
    }

    if (eventName === AppMessages.EVENT_CONTEXTINIT) {
      return this.contextInit(widget);
    }

    if (eventName === AppMessages.EVENT_FIND_ALL_STATE) {
      return this.findAllAppState(widget);
    }

    if (eventName === AppMessages.EVENT_GET_STATE) {
      return this.getAppState(widget);
    }

    if (eventName === AppMessages.EVENT_SAVE_STATE) {
      return this.saveAppState(widget);
    }

    throw new Error(`Could not find a message channel for event ${eventName}`);
  };

  /**
   * @param {WidgetConfiguration} widget
   * @return {MessageChannel}
   */
  contextInit = (widget) => {

    const replyHandler = (widget, widgetWindow, context) => {
      const value = context ? context : null;
      postRobot.send(widgetWindow, AppMessages.EVENT_CONTEXTINIT, value);
    };

    const defaultHandler = (widget, message, reply) => reply(null);
    return new AppMessageGateway.MessageChannel(AppMessages.EVENT_CONTEXTINIT, widget, replyHandler, defaultHandler);
  };

  /**
   * @param {WidgetConfiguration} widget
   * @return {MessageChannel}
   */
  findAllAppState = (widget) => {

    const replyHandler = (widget, widgetWindow, stateList) => {
      const value = stateList ? stateList : [];
      postRobot.send(widgetWindow, AppMessages.EVENT_FIND_ALL_STATE, value);
    };

    //TODO handler postMessages without a payload (second argument)
    const { appstoreDispatcher } = this;
    const defaultHandler = (widget, message, reply) => {
      appstoreDispatcher.dispatchFindAllAppState(widget.appConfig.id, (app, state) => reply(state))
    };

    return new AppMessageGateway.MessageChannel(AppMessages.EVENT_FIND_ALL_STATE, widget, replyHandler, defaultHandler);
  };

  /**
   * @param {WidgetConfiguration} widget
   * @return {MessageChannel}
   */
  getAppState = (widget) => {

    const replyHandler = (widget, widgetWindow, state) => {
      const value = state ? JSON.parse(state.value) : null;
      postRobot.send(widgetWindow, AppMessages.EVENT_GET_STATE, value);
    };

    const { appstoreDispatcher } = this;
    const defaultHandler = (widget, state, reply) => {
      const { name, scope } = state;
      appstoreDispatcher.dispatchGetAppState(widget.appConfig.id, name, scope, (app, state) => { reply(state); });
    };

    return new AppMessageGateway.MessageChannel(AppMessages.EVENT_GET_STATE, widget, replyHandler, defaultHandler);
  };

  /**
   * @param {WidgetConfiguration} widget
   * @return {MessageChannel}
   */
  saveAppState = (widget) => {

    const replyHandler = (widget, widgetWindow, state) => {
      const value = state ? JSON.parse(state.value) : null;
      postRobot.send(widgetWindow, AppMessages.EVENT_SAVE_STATE, value);
    };

    const { appstoreDispatcher } = this;
    const defaultHandler = (widget, state, reply) => {
      appstoreDispatcher.dispatchSaveState(widget.appConfig.id, state, (app, state) => reply(state))
    };

    return new AppMessageGateway.MessageChannel(AppMessages.EVENT_SAVE_STATE, widget, replyHandler, defaultHandler);
  };

}

export default AppMessageGateway;
