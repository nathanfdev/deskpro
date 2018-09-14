import Immutable from 'immutable';
import { ContextProps } from './WidgetProps';

export class Context {

  /**
   * @param {Object} props
   * @return {Context}
   */
  static fromProps(props) { return new Context(props); }

  /**
   * @param {String} id
   * @param {String} type
   * @param {String} entityId
   * @param {String} locationId
   * @param {String} pageId
   * @param {String} tabId
   * @param {String} tabUrl
   * @param {Object} undeclaredProps
   */
  constructor({ id, type, entityId, locationId, pageId, tabId, tabUrl, ...undeclaredProps }) {
    const props = { id, type, entityId, locationId, pageId, tabId, tabUrl, ...undeclaredProps };
    this.props = Immutable.fromJS(props);
  }

  /**
   * @return {String}
   */
  get id() { return this.props.get('id'); }

  /**
   * @return {String}
   */
  get locationId() { return this.props.get('locationId'); }

  /**
   * The type of entity associated with this context
   *
   * @return {String}
   */
  get type() { return this.props.get('type'); }

  /**
   * The id of the entity associated with this context
   *
   * @return {String}
   */
  get entityId() { return this.props.get('entityId'); }

  /**
   * The id of the UI Tab that displays this context
   *
   * @return {String}
   */
  get pageId() { return this.props.get('pageId'); }

  /**
   * The id of the UI Tab that displays this context
   *
   * @return {String}
   */
  get tabId() { return this.props.get('tabId'); }

  /**
   * The url of the UI Tab that displays this context
   *
   * @return {String}
   */
  get tabUrl() { return this.props.get('tabUrl'); }


  get widgetProps() {
    const propsJS = this.props.toJS();
    return new ContextProps(propsJS);
  }

  toJS = () => this.props.toJS();
}
