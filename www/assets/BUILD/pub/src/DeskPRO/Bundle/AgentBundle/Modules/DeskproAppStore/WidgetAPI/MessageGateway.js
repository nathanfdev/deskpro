import postRobot from 'post-robot/src';
import * as Messages from './Messages';
import MessageBroker from './MessageBroker';
import MessageChannel from './MessageChannel';

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

class MessageGateway
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
    const gateway = new MessageGateway(reduxDispatcher);
    const broker = new MessageGateway.MessageBroker(gateway);

    return broker.bindWidget.bind(broker);
  };

  /**
   * @param {ReduxActionDispatcher} reduxDispatcher
   */
  constructor (reduxDispatcher)
  {
    this.appstoreDispatcher = reduxDispatcher;
    //this.handlerRegistrar = (eventName, widgetId, requestHandler) => registerRequestHandler(eventName, widgetId, requestHandler);
  }

  /**
   * @param eventName
   * @return {MessageChannel}
   */
  messageChannelForEvent = (eventName, widget) =>
  {
    if (! Messages.isEventName(eventName)) {
      throw new Error(`${eventName} is not a known event name`);
    }

    if (eventName === Messages.EVENT_CONTEXTINIT) {
      return this.contextInit(widget);
    }

    if (eventName === Messages.EVENT_STATE_FIND) {
      return this.findAppState(widget);
    }

    if (eventName === Messages.EVENT_STATE_GET) {
      return this.getAppState(widget);
    }

    if (eventName === Messages.EVENT_STATE_SAVE) {
      return this.saveAppState(widget);
    }

    if (eventName === Messages.EVENT_STATE_CREATE) {
      return this.createAppState(widget);
    }

    if (eventName === Messages.EVENT_STATE_UPDATE) {
      return this.updateAppState(widget);
    }

    if (eventName === Messages.EVENT_STATE_DELETE) {
      return this.deleteAppState(widget);
    }

    if (eventName === Messages.EVENT_USER_GET) {
      return this.getUser(widget);
    }

    throw new Error(`Could not find a message channel for event ${eventName}`);
  };

  /**
   * @param {WidgetConfiguration} widget
   * @return {MessageChannel}
   */
  contextInit = (widget) => {
    const messageType = Messages.EVENT_CONTEXTINIT;

    const replyHandler = (widget, widgetWindow, context) => {
      const value = context ? context : null;
      postRobot.send(widgetWindow, messageType, value);
    };

    const defaultHandler = (widget, message, reply) => reply(null);
    return new MessageGateway.MessageChannel(messageType, widget, replyHandler, defaultHandler, registerRequestHandler);
  };

  /**
   * @param {WidgetConfiguration} widget
   * @return {MessageChannel}
   */
  findAppState = (widget) => {
    const messageType = Messages.EVENT_STATE_FIND;

    const replyHandler = (widget, widgetWindow, stateList) => {
      const value = stateList ? stateList : [];
      postRobot.send(widgetWindow, messageType, value);
    };

    //TODO handler postMessages without a payload (second argument)
    const { appstoreDispatcher } = this;
    const defaultHandler = (widget, message, reply) => {
      appstoreDispatcher.dispatchFindAppState(widget.appConfig.instanceId, (app, state) => reply(state))
    };

    return new MessageGateway.MessageChannel(messageType, widget, replyHandler, defaultHandler, registerRequestHandler);
  };

  /**
   * @param {WidgetConfiguration} widget
   * @return {MessageChannel}
   */
  getAppState = (widget) => {
    const messageType = Messages.EVENT_STATE_GET;

    const replyHandler = (widget, widgetWindow, state) => {
      const value = state ? JSON.parse(state.value) : null;
      postRobot.send(widgetWindow, messageType, value);
    };

    const { appstoreDispatcher } = this;
    const defaultHandler = (widget, state, reply) => {
      const { name, scope } = state;
      appstoreDispatcher.dispatchGetAppState(widget.appConfig.instanceId, name, scope, (app, state) => { reply(state); });
    };

    return new MessageGateway.MessageChannel(messageType, widget, replyHandler, defaultHandler, registerRequestHandler);
  };

  /**
   * @param {WidgetConfiguration} widget
   * @return {MessageChannel}
   */
  saveAppState = (widget) => {
    const messageType = Messages.EVENT_STATE_SAVE;

    const replyHandler = (widget, widgetWindow, state) => {
      const value = state ? JSON.parse(state.value) : null;
      postRobot.send(widgetWindow, messageType, value);
    };

    const { appstoreDispatcher } = this;
    const defaultHandler = (widget, state, reply) => {
      appstoreDispatcher.dispatchSaveAppState(widget.appConfig.instanceId, state, (app, state) => reply(state))
    };

    return new MessageGateway.MessageChannel(messageType, widget, replyHandler, defaultHandler, registerRequestHandler);
  };

  /**
   * @param {WidgetConfiguration} widget
   * @return {MessageChannel}
   */
  createAppState = (widget) => {
    const messageType = Messages.EVENT_STATE_CREATE;

    const replyHandler = (widget, widgetWindow, state) => {
      const value = state ? JSON.parse(state.value) : null;
      postRobot.send(widgetWindow, messageType, value);
    };

    const { appstoreDispatcher } = this;
    const defaultHandler = (widget, state, reply) => {
      appstoreDispatcher.dispatchCreateAppState(widget.appConfig.instanceId, state, (app, state) => reply(state))
    };

    return new MessageGateway.MessageChannel(messageType, widget, replyHandler, defaultHandler, registerRequestHandler);
  };

  /**
   * @param {WidgetConfiguration} widget
   * @return {MessageChannel}
   */
  updateAppState = (widget) => {
    const messageType = Messages.EVENT_STATE_UPDATE;

    const replyHandler = (widget, widgetWindow, state) => {
      const value = state ? JSON.parse(state.value) : null;
      postRobot.send(widgetWindow, messageType, value);
    };

    const { appstoreDispatcher } = this;
    const defaultHandler = (widget, state, reply) => {
      appstoreDispatcher.dispatchUpdateAppState(widget.appConfig.instanceId, state, (app, state) => reply(state))
    };

    return new MessageGateway.MessageChannel(messageType, widget, replyHandler, defaultHandler, registerRequestHandler);
  };

  /**
   * @param {WidgetConfiguration} widget
   * @return {MessageChannel}
   */
  deleteAppState = (widget) => {
    const messageType = Messages.EVENT_STATE_DELETE;

    const replyHandler = (widget, widgetWindow, state) => {
      const value = state ? JSON.parse(state.value) : null;
      postRobot.send(widgetWindow, messageType, value);
    };

    const { appstoreDispatcher } = this;
    const defaultHandler = (widget, state, reply) => {
      const { name } = state;
      appstoreDispatcher.dispatchDeleteAppState(widget.appConfig.instanceId, name, (app, state) => reply(state))
    };

    return new MessageGateway.MessageChannel(messageType, widget, replyHandler, defaultHandler, registerRequestHandler);
  };

  /**
   * @param {WidgetConfiguration} widget
   * @return {MessageChannel}
   */
  getUser = (widget) => {
    const messageType = Messages.EVENT_USER_GET;

    const replyHandler = (widget, widgetWindow, user) => {
      postRobot.send(widgetWindow, messageType, user);
    };

    const { appstoreDispatcher } = this;
    const defaultHandler = (widget, message, reply) => {
      appstoreDispatcher.dispatchGetUser(widget.appConfig.instanceId, (app, user) => reply(user));
    };

    return new MessageGateway.MessageChannel(messageType, widget, replyHandler, defaultHandler, registerRequestHandler);
  };

}

export default MessageGateway;
