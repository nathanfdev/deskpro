import React, { PropTypes } from 'react';
import * as xcomponent from 'xcomponent/dist/xcomponent';

import { Widget } from '../Domain/Widget'
import { WidgetMessage } from '../WidgetMessage'

/**
 * @param {ParentComponent} parentComponent
 * @param {WidgetConfiguration} widgetConfig
 * @return {Widget}
 */
export const createWidget = (parentComponent, widgetConfig) =>
{
  const windowId = ['xcomponent', parentComponent.props.uid].join('-');
  return new Widget({ configuration: widgetConfig, windowId });
};

/**
 * This container represents the integration point between an external app and deskpro.
 * It handles a list of apps within the same app context, managing their lifecycle and communication, behaving in this
 * respect as a router (routing and transforming deskpro events / messages to app components)
 */
class DeskproAppContainer extends React.Component
{
  static propTypes = {
    widgetsConfigList: PropTypes.array.isRequired
    , dispatchIncomingWidgetMessage: PropTypes.func.isRequired
    , context: PropTypes.object.isRequired
    , configuration: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.widgets = [];
  }

  /**
   * @param {String} id
   * @return {WidgetConfiguration|null}
   */
  findWidgetConfigByWidgetId = id => {
    const { widgetsConfigList } = this.props;
    const found = widgetsConfigList.filter(widget => widget.id === id);

    return found.length === 1 ? found[0] : null;
  };

  /**
   * @param {WidgetConfiguration} widgetConfig
   * @return {Widget}
   */
  findWidgetByWidgetConfig = widgetConfig => {
    const found = this.widgets.filter(widget =>  widget.configuration === widgetConfig);
    return found.length === 1 ? found[0] : null;
  };

  /**
   * Renders the container and all the apps
   *
   * @returns {XML}
   */
  render() {
    const { widgetsConfigList } = this.props;
    if (widgetsConfigList && widgetsConfigList.length > 0) {
      return this.renderApp();
    }

    return this.renderEmpty();
  }

  /**
   * Renders an empty div
   *
   * @returns {XML}
   */
  renderEmpty() { return (<div />); }

  /**
   * Renders all the apps
   *
   * @returns {XML}
   */
  renderApp() {
    const { widgetsConfigList } = this.props;
    const components = widgetsConfigList.map(widget => this.createReactElement(widget));

    return React.createElement('div', {}, components);
  }

  /**
   * @param {WidgetConfiguration} widgetConfig
   * @return {ReactElement}
   */
  createReactElement = (widgetConfig) =>
  {
    // widget properties

    const onEnter = this.onXComponentEnter.bind(this);
    const widgetProps = {
      widgetId: widgetConfig.id,
      // xcomponent changes the scope of the onEnter callback to that of the ParentComponent instance and does not provide
      // any other parameters so we resort to this type of closure to get a hold of the ParentComponent instance
      onEnter: function () { onEnter(this) },
      onDpMessage: this.onXComponentMessage.bind(this, widgetConfig),
    };

    // instance properties

    const instanceProps = {
      appId: widgetConfig.appConfig.applicationId,
      appTitle: widgetConfig.appConfig.applicationTitle,
      appPackageName: widgetConfig.appConfig.applicationPackageName,
      instanceId: widgetConfig.appConfig.instanceId,
    };

    // context properties

    const { context } = this.props;
    const contextProps = {
      contextType: context.type.toString(),
      contextEntityId: context.entityId.toString(),
      contextLocationId: context.locationId.toString(),
      contextTabId: context.tabId.toString()
    };

    const reactProps = { key: widgetConfig.id, ...widgetProps, ...instanceProps, ...contextProps };

    const xcomponentInstance = xcomponent.create(widgetConfig.xcomponentConfig);
    const reactClass = xcomponentInstance.react;
    return React.createElement(reactClass, reactProps);
  };

  /**
   * @param {WidgetConfiguration} widgetConfiguration
   * @param {String} eventName
   * @param {*} widgetMessage
   */
  onXComponentMessage = (widgetConfiguration, eventName, widgetMessage) =>
  {
    const { dispatchIncomingWidgetMessage } = this.props;
    const widget = this.findWidgetByWidgetConfig(widgetConfiguration);
    dispatchIncomingWidgetMessage(eventName, widgetMessage, widget);
  };

  /**
   * Handler for the onEnter event sent by the parentComponent of an xcomponent
   *
   * @param {ParentComponent} parentComponent
   */
  onXComponentEnter = (parentComponent) =>
  {
    const { widgetId } = parentComponent.props;
    // TODO handle case for uknown parentComponent widget
    const widgetConfiguration = this.findWidgetConfigByWidgetId(widgetId);

    const widget = createWidget(parentComponent, widgetConfiguration);
    this.widgets.push(widget);
  };
}

export default DeskproAppContainer;
