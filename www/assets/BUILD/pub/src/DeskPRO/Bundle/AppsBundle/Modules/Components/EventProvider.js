/**
 * This class acts as a factory for the different parts of an event.
 * Event providers are usually passed around as needed to components or services which implement the actual event
 * listening, for instance a postrobot adapter
 */
export class EventProvider {
  /**
   * @param {string}  urn
   * @param {function} handler
   */
  constructor({ urn, handler })  {
    this.props = { urn, handler };
  }

  /**
   * @param {WidgetConfiguration} config
   * @return {string}
   */
  getName(config)  {
    return `${this.props.urn}?widgetId=${config.id}`;
  }

  /**
   * @param {WidgetConfiguration}  config
   * @return {Function}
   */
  getHandler(config)  { // eslint-disable-line no-unused-vars
    return this.props.handler;
  }
}
