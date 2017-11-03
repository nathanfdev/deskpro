export class Widget {
  /**
   * @param {WidgetConfiguration} configuration
   * @param {String} windowId
   */
  constructor({ configuration, windowId }) {
    this.props = { configuration, windowId };
  }

  /**
   * @return {String}
   */
  get id() { return this.props.configuration.id; }

  /**
   * @return {String}
   */
  get instanceId() { return this.props.configuration.appConfig.instanceId; }

  /**
   * @return {String}
   */
  get applicationId() { return this.props.configuration.appConfig.applicationId; }

  /**
   * @return {WidgetConfiguration}
   */
  get configuration() { return this.props.configuration; }

  /**
   * @return {String}
   */
  get windowId() { return this.props.windowId; }

}
