import { dispatchIncomingWidgetMessage, parseIncomingWidgetMessageJS, addWidgetEventListener } from '../WidgetMessage';

export class DeskproAppContainerProps {
  /**
   * @param {Context} context
   * @param {Array<WidgetConfiguration>} widgetsConfigList
   * @return {{widgetsConfigList: *, context: *, dispatchIncomingWidgetMessage: dispatchIncomingWidgetMessage, parseIncomingWidgetMessageJS, addWidgetEventListener}}
   */
  static create({ context, widgetsConfigList })  {
    return {
      widgetsConfigList,
      context,
      dispatchIncomingWidgetMessage,
      parseIncomingWidgetMessageJS,
      addWidgetEventListener
    };
  }
}
