import { WidgetDOM } from '../WidgetDOM'
import { addWidgetEventListener } from '../WidgetMessage'

export class AppServices
{
  /**
   * @param {DpApi} api
   * @param {Window} window
   * @param {EventSubscribersRegistry} widgetEventRegistry
   */
  constructor({ api, window }) {
    this.props = { api, window };
    this.state = { eventSubscribers: new Map() };
  }

  /**
   * @return {DpApi}
   */
  get api() { return this.props.api; }

  /**
   * @return {Orb.Class|DeskPRO.Agent.WindowElement.TabBar}
   */
  get tabs() { return DeskPRO_Window.TabBar; }

  /**
   * @return {Window}
   */
  get window() { return this.props.window; }

  /**
   * @return {*}
   */
  get $() { return this.window.$; }

  get widgetDOM() {
    const { document } = this.props.window;
    return new WidgetDOM({ document });
  }

  addEventListener = (eventName, widget) => addWidgetEventListener(eventName, widget, this);

  showNotification = notification => {
    if (typeof notification === 'string') {
      //console.log('will show notification', notification);
      this.props.window.alert(notification);
      return;
    }

    throw new Error('unknown notification type');
  };

}
