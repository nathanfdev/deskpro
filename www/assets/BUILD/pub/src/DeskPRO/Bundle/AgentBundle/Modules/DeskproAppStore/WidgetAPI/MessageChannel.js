class MessageChannel
{
  /**
   * @param {String} eventName
   * @param {WidgetConfiguration} widget
   * @param {Function} replyHandler
   * @param {Function} defaultRequestHandler
   * @param {Function} handlerRegistrar
   */
  constructor(eventName, widget, replyHandler, defaultRequestHandler, handlerRegistrar) {
    this.eventName = eventName;
    this.widget = widget;
    this.replyHandler = replyHandler;
    this.defaultRequestHandler = defaultRequestHandler;
    this.handlerRegistrar = handlerRegistrar;
  }

  bindWithHandler = (widgetWindow, id, requestHandler) => {

    const { widget, replyHandler, eventName } = this;
    const reply = message => replyHandler(widget, widgetWindow, message);
    const dispatcher = (widgetId, message) => {
      requestHandler(widget, message, reply)
    };

    this.handlerRegistrar(eventName, id, dispatcher)
  };

  bind = (widgetWindow, id) => {

    const { defaultRequestHandler } = this;
    this.bindWithHandler(widgetWindow, id, defaultRequestHandler);
  }
}

export default MessageChannel;
