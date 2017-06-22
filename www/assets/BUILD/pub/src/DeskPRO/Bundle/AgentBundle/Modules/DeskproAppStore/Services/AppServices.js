import { WidgetDOM } from '../WidgetDOM'
import { addWidgetEventListener } from '../WidgetMessage'
import { InstanceProxyClient, DPAPIClient } from '../HttpClients'

export class AppServices
{
  /**
   * @param {Http} api
   * @param {Window} window
   */
  constructor({ api, window }) {
    this.props = { api, window };
    this.state = { eventSubscribers: new Map() };
  }

  /**
   * @param {Widget} widget
   * @return {InstanceProxyClient}
   */
  getProxyClient({ widget }) {
    const { api } = this.props;
    const apiClient = new DPAPIClient({ api });

    return new InstanceProxyClient({ apiClient, instanceId: widget.instanceId });
  }

  get dpClient() {
    const { api } = this.props;
    return new DPAPIClient({ api });
  }

  /**
   * @return {Http}
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
