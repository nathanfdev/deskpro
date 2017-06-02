import { WidgetDOM } from '../WidgetDOM'

export class AppServices
{
  /**
   * @param {DpApi} api
   * @param {Window} window
   */
  constructor({ api, window }) {
    this.props = { api, window };
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

  /**
   * @return {*}
   */
  get $() { return this.window.$; }

  showNotification = notification => {
    if (typeof notification === 'string') {
      console.log('will show some notification');
      this.props.window.alert(notification);
      return;
    }

    throw new Error('unknown notification type');
  };
}
