import Immutable from 'immutable';
import { ContextProps } from './WidgetProps';

export class Context {

  /**
   * @param {Object}props
   * @return {Context}
   */
  static fromProps(props) { return new Context(props); }

  /**
   * @param {String} id
   * @param {String} type
   * @param {String} entityId
   * @param {String} locationId
   * @param {String} tabId
   * @param {String} tabUrl
   */
  constructor({ id, type, entityId, locationId, tabId, tabUrl }) {
    const props = { id, type, entityId, locationId, tabId, tabUrl };
    this.props = Immutable.fromJS(props);
  }

  /**
   * @return {String}
   */
  get id() { return this.props.get('id'); }

  /**
   * @return {String}
   */
  get type() { return this.props.get('type'); }

  /**
   * @return {String}
   */
  get entityId() { return this.props.get('entityId'); }

  /**
   * @return {String}
   */
  get locationId() { return this.props.get('locationId'); }

  /**
   * @return {String}
   */
  get tabId() { return this.props.get('tabId'); }

  /**
   * @return {String}
   */
  get tabUrl() { return this.props.get('tabUrl'); }

  get widgetProps() {
    const { type, entityId, locationId, tabId, tabUrl } = this.toJS();
    return new ContextProps({ type, entityId, locationId, tabId, tabUrl });
  }

  toJS = () => this.props.toJS();
}
