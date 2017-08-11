import React, { PropTypes } from 'react';
import * as xcomponent from 'xcomponent/dist/xcomponent';
import postRobot from 'post-robot/dist/post-robot';

import { Widget } from '../Domain/Widget';
import * as WidgetDOM from '../WidgetDOM';

import { ContainerEvents } from './ContainerEvents';

/**
 * @param {ParentComponent} parentComponent
 * @param {WidgetConfiguration} widgetConfig
 * @return {Widget}
 */
export const createWidget = (parentComponent, widgetConfig) => {
  const windowId = ['xcomponent', parentComponent.props.uid].join('-');
  return new Widget({ configuration: widgetConfig, windowId });
};

/**
 * @param {Array} list
 * @param {function} filter
 * @return {null|*}
 */
const find = (list, filter) => {
  const found = list.filter(filter);
  return found.length === 1 ? found[0] : null;
};

const removeMatching = (list, filter) => {
  let iterations = list.length;
  const matching = [];

  while (iterations > 0) {
    iterations -= 1;
    const item = list.pop();
    const isMatching = filter(item);

    if (isMatching) {
      matching.unshift(item);
    } else {
      list.unshift(item);
    }
  }

  return matching;
};

/* eslint class-methods-use-this: ["error", { "exceptMethods": ["onWidgetMouseEventMessage"] }] */


/**
 * This container represents the integration point between an external app and deskpro.
 * It handles a list of apps within the same app context, managing their lifecycle and communication, behaving in this
 * respect as a router (routing and transforming deskpro events / messages to app components)
 */
class DeskproAppContainer extends React.Component {

  static propTypes = {
    widgetsConfigList:             PropTypes.array.isRequired,
    context:                       PropTypes.object.isRequired,
    dispatchIncomingWidgetMessage: PropTypes.func.isRequired,
    addWidgetEventListener:        PropTypes.func.isRequired,
    parseIncomingWidgetMessageJS:  PropTypes.func.isRequired
  };

  /**
   * Renders an empty div
   *
   * @returns {XML}
   */
  static renderEmpty() { return (<div />); }

  /**
   * @param {DeskproAppContainer} container
   * @param {WidgetConfiguration} widgetConfig
   * @param {Context} context
   *
   * @return {ReactElement}
   */
  static createWidgetElement(container, widgetConfig, context) {
    // widget properties

    const onXComponentEnter = container.onXComponentEnter.bind(container);
    const onXComponentClose = container.onXComponentClose.bind(container);
    const onDpMessage = container.onWidgetMessageReceive.bind(container, widgetConfig);

    const xcomponentProps = {
      widgetId: widgetConfig.id,
      onEnter() {
        // xcomponent changes the scope of the callback to that of the ParentComponent instance and does not provide
        // any other parameters so we resort to this type of closure to get a hold of the ParentComponent instance
        // when the function executes, this points to the ParentComponent instance
        onXComponentEnter(this);
      },

      onClose() {
        // xcomponent changes the scope of the callback to that of the ParentComponent instance and does not provide
        // any other parameters so we resort to this type of closure to get a hold of the ParentComponent instance
        // when the function executes, this points to the ParentComponent instance
        onXComponentClose(this);
      },

      onDpMessage
    };

    const reactProps = {
      key:           widgetConfig.id,
      ...xcomponentProps,
      instanceProps: widgetConfig.widgetProps.toJS(),
      contextProps:  context.widgetProps.toJS()
    };

    const xcomponentInstance = xcomponent.create(widgetConfig.xcomponentConfig);
    const reactClass = xcomponentInstance.react;
    return React.createElement(reactClass, reactProps);
  }

  constructor(props) {
    super(props);
    this.widgets = [];
    this.widgetRemoveListeners = [];
  }

  /**
   * @param {Widget} widget
   * @param {String} eventName
   * @param {WidgetRequest} widgetMessage
   */
  onWidgetMouseEventMessage(widget, eventName, widgetMessage) { /* empty on purpose */ } // eslint-disable-line no-unused-vars

  /**
   * @param {Widget} widget
   * @param {String} eventName
   * @param {WidgetRequest|WidgetResponse} widgetMessage
   */
  onWidgetAppMessage(widget, eventName, widgetMessage)  {
    const { dispatchIncomingWidgetMessage } = this.props;
    dispatchIncomingWidgetMessage(eventName, widgetMessage, widget);
  }

  // generic widget message handlers

  onWidgetEventSubscribe(widgetConfiguration, eventName, eventSubscriber) {
    const widget = find(this.widgets, aWidget => aWidget.configuration === widgetConfiguration);
    if (!widget) { return null; }

    const unsubscribe = eventSubscriber(eventName, widget);
    this.widgetRemoveListeners.push({ widget, removeListener: unsubscribe });
    return null;
  }

  onWidgetMessageReceive(widgetConfiguration, eventName, message) {
    // find the widget
    const initiatorWidget = find(this.widgets, widget => widget.configuration === widgetConfiguration);
    if (!initiatorWidget) {
      throw new Error('failed to dispatch incoming message: unrecognized widget');
    }

    const { parseIncomingWidgetMessageJS } = this.props;
    const widgetMessage = parseIncomingWidgetMessageJS(message);

    if (eventName === ContainerEvents.EVENT_WINDOW_MOUSEEVENT) {
      this.onWidgetMouseEventMessage(initiatorWidget, eventName, widgetMessage);
    } else {
      this.onWidgetAppMessage(initiatorWidget, eventName, widgetMessage);
    }
  }

  /**
   * @param {WidgetConfiguration} widgetConfiguration
   * @param {String} eventName
   * @param {WidgetRequest|WidgetResponse} widgetMessage
   */
  onWidgetMessageSend(widgetConfiguration, eventName, widgetMessage) {
    const widget = find(this.widgets, aWidget => aWidget.configuration === widgetConfiguration);
    if (!widget) { // do not throw exceptions yet, silently ignore
      return null;
    }

    const widgetWindow = WidgetDOM.findWidgetWindow(widget, window.document);
    if (widgetWindow) {
      postRobot.send(widgetWindow, eventName, widgetMessage.toJS());
      return null;
    }

    throw new Error('can not find widget window');
  }

  /**
   * Handler for the onEnter event sent by the parentComponent of an xcomponent component
   *
   * @param {ParentComponent} parentComponent
   */
  onXComponentEnter = (parentComponent) =>  {
    const { widgetId } = parentComponent.props;
    const { widgetsConfigList, addWidgetEventListener } = this.props;

    const widgetConfiguration = find(widgetsConfigList, widget => widget.id.toString() === widgetId.toString());
    if (widgetConfiguration) {
      const widget = createWidget(parentComponent, widgetConfiguration);
      const removeListeners = [
        addWidgetEventListener(widget.id, this.onWidgetMessageSend.bind(this)),
        addWidgetEventListener(`subscribe.${widget.id}`, this.onWidgetEventSubscribe.bind(this))
      ];

      this.registerWidget(widget, removeListeners);
      return null;
    }

    // TODO better handling of the case for unknown parentComponent widget
    throw new Error(`failed to handle widget registering: configuration for widget id: ${widgetId} not found`);
  };

  /**
   * Handler for the onClose event sent by the parentComponent of an xcomponent component
   *
   * @param {ParentComponent} parentComponent
   */
  onXComponentClose = (parentComponent) =>  {
    const { widgetId } = parentComponent.props;
    const { widgets } = this;

    const widget = find(widgets, aWidget => aWidget.id.toString() === widgetId.toString());
    if (widget) {
      this.unregisterWidget(widget);
      return null;
    }

    // TODO better handling of the case for unknown parentComponent widget
    throw new Error(`failed to handle widget closing : widget id: ${widgetId} not found`);
  };

  /**
   * @param {Widget} widget
   * @param {Array<function>} removeListeners
   */
  registerWidget = (widget, removeListeners) =>  {
    // store the widget
    this.widgets.push(widget);
    // index the widget remove listeners
    const widgetRemoveListeners = removeListeners.map(removeListener => ({ widget, removeListener }));
    this.widgetRemoveListeners = this.widgetRemoveListeners.concat(widgetRemoveListeners);
  };

  /**
   * @param {Widget} widget
   */
  unregisterWidget = (widget) => {
    //
    // remove widget
    removeMatching(this.widgets, aWidget => aWidget === widget);

    // remove listeners
    const invokeRemoveListener = ({ removeListener }) => removeListener();
    removeMatching(this.widgetRemoveListeners, listener => listener.widget === widget).each(invokeRemoveListener);
  };

  /**
   * Renders all the apps
   *
   * @returns {XML}
   */
  renderApp() {
    const { widgetsConfigList, context } = this.props;
    const components = widgetsConfigList.map(widget => DeskproAppContainer.createWidgetElement(this, widget, context));

    return React.createElement('div', {}, components);
  }

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

    return DeskproAppContainer.renderEmpty();
  }


}

export default DeskproAppContainer;
