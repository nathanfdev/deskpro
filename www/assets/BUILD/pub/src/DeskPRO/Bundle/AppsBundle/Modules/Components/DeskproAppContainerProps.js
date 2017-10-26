import { dispatchIncomingWidgetMessage as defaultDispatchIncomingWidgetMessage, parseIncomingWidgetMessageJS, addWidgetEventListener } from '../WidgetMessage';

export class DeskproAppContainerProps {
  /**
   * @param {Context} context
   * @param {Array<WidgetConfiguration>} widgetsConfigList
   * @param {function} [dispatchIncomingWidgetMessage]
   * @return {{widgetsConfigList: *, context: *, dispatchIncomingWidgetMessage: dispatchIncomingWidgetMessage, parseIncomingWidgetMessageJS, addWidgetEventListener}}
   */
  static create({ context, widgetsConfigList, dispatchIncomingWidgetMessage })  {
    let dispatcher = defaultDispatchIncomingWidgetMessage;
    if (typeof dispatchIncomingWidgetMessage === 'function') {
      dispatcher = (eventName, widgetMessage, widget) => dispatchIncomingWidgetMessage(eventName, widgetMessage, widget, defaultDispatchIncomingWidgetMessage);
    }

    return {
      widgetsConfigList,
      context,
      dispatchIncomingWidgetMessage: dispatcher,
      parseIncomingWidgetMessageJS,
      addWidgetEventListener
    };
  }

}
