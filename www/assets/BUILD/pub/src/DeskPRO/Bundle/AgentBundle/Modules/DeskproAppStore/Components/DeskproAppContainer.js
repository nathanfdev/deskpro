import React, { PropTypes } from 'react';
import * as xcomponent from 'xcomponent/src';

import * as AppMessages from '../Services/AppMessages'

/**
 * This container represents the integration point between an external app and deskpro.
 * It handles a list of apps within the same app context, managing their lifecycle and communication, behaving in this
 * respect as a router (routing and transforming deskpro events / messages to app components)
 */
class DeskproAppContainer extends React.Component {

  static propTypes = {
    context: PropTypes.object.isRequired
    , configuration: PropTypes.object.isRequired
    , appstoreDispatcher: PropTypes.object.isRequired
    , widgets: PropTypes.array.isRequired
    , widgetMessageRouter: PropTypes.func.isRequired
    , widgetMessageBroker: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.components = new Map();
  }

  /**
   * Renders the container and all the apps
   *
   * @returns {XML}
   */
  render() {
    const { widgets } = this.props;
    if (widgets) {
      return this.renderApp();
    }

    return this.renderEmpty();
  }

  /**
   * Renders an empty div
   *
   * @returns {XML}
   */
  renderEmpty() {
    return (<div></div>)
  }

  /**
   * Renders all the apps
   *
   * @returns {XML}
   */
  renderApp() {
    const { widgets } = this.props;
    const components = widgets.map( widget => this.createReactElement(widget.id, widget.config));

    return (<div> {components} </div> );
  }

  /**
   * @param {String} widgetId
   * @param {WidgetConfiguration} widgetConfig
   * @return {ReactElement}
   */
  createReactElement = (widgetId, widgetConfig) =>
  {
    const { widgetMessageRouter } = this.props;
    const reactClass = xcomponent.create(widgetConfig.xcomponentConfig).react;
    const reactProps = {
      key: widgetId,
      onEnter: DeskproAppContainer.createOnXComponentEnterListener(this),
      //app: widgetConfig.appConfig.id,
      widgetId,
      onDpMessage: (eventName, message) => widgetMessageRouter(eventName, message)
    };

    return React.createElement(reactClass, reactProps);
  };

  /**
   * @param {WidgetConfiguration} widget
   * @param {Object} msg
   * @param {Function} reply
   */
  onXComponentDPContextInit = (widget, msg, reply) =>
  {
    const { context } = this.props;
    reply(context);
  };

  /**
   * Handler for the onEnter event sent by the parentComponent of an xcomponent
   *
   * @param {ParentComponent} parentComponent
   */
  onXComponentEnter = (parentComponent) =>
  {
    const widgetWindow = parentComponent.iframe.contentWindow;
    const { widgetId } = parentComponent.props;

    const { configuration, appstoreDispatcher, widgetMessageBroker, widgets } = this.props;
    const widget = widgets.filter(widget => widget.id === widgetId)[0];

    //TODO handle case for uknown parentComponent widget

    this.components.set(widgetId, parentComponent);
    appstoreDispatcher.dispatchAppMounted(configuration.targetType, widget.config, widget.id);

    const subscribeTo = [
      { eventName: AppMessages.EVENT_CONTEXTINIT, requestHandler: this.onXComponentDPContextInit },
      AppMessages.EVENT_GET_STATE,
      AppMessages.EVENT_SAVE_STATE,
      AppMessages.EVENT_FIND_ALL_STATE
    ];
    widgetMessageBroker(widget.config, widgetWindow, widgetId, subscribeTo);
  };

  /**
   * Creates an onEnter callback for xcomponent
   *
   * xcomponent changes the scope of the onEnter callback to that of the ParentComponent instance and does not provide
   * any other parameters so we resort to this type of closure to get a hold of the ParentComponent instance
   *
   * @param {DeskproAppContainer} container
   * @returns {Function}
   */
  static createOnXComponentEnterListener(container)
  {
    return function () { container.onXComponentEnter(this); }
  }
}

export default DeskproAppContainer;
