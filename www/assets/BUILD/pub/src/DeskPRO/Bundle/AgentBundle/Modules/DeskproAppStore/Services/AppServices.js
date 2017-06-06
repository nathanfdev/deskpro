import { WidgetDOM } from '../WidgetDOM'

export class AppServices
{
  /**
   * @param {DpApi} api
   * @param {Window} window
   * @param {EventSubscribersRegistry} widgetEventRegistry
   */
  constructor({ api, window, widgetEventRegistry }) {
    this.props = { api, window, widgetEventRegistry };
    this.state = { eventSubscribers: new Map() };
  }

  /**
   * @return {DpApi}
   */
  get api() { return this.props.api; }

  /**
   * @return {Window}
   */
  get window() { return this.props.window; }

  get widgetDOM() {
    const { document } = this.props.window;
    return new WidgetDOM({ document });
  }

  get widgetEventRegistry() { return this.props.widgetEventRegistry; }

  /**
   * @return {*}
   */
  get $() { return this.window.$; }

  showNotification = notification => {
    if (typeof notification === 'string') {
      //console.log('will show notification', notification);
      this.props.window.alert(notification);
      return;
    }

    throw new Error('unknown notification type');
  };

}
