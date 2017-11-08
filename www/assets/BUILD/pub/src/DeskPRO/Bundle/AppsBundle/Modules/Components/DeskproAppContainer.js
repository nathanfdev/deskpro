import PropTypes from 'prop-types';
import React from 'react';
import * as postRobot from 'post-robot';


import { Widget } from '../Domain/Widget';
import * as WidgetDOM from '../WidgetDOM';

import { ContainerEvents } from './ContainerEvents';
import { WidgetIframe } from './WidgetIframe';

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
   * @param {WidgetConfiguration} widgetConfiguration
   *
   * @return {Object}
   */
  static mapWidgetConfigurationToReactComponent(widgetConfiguration) {
    return (<WidgetIframe
      id={`urn:deskpro:widget?widgetId=${widgetConfiguration.id}`}
      url={widgetConfiguration.getUrl()}
    />);
  }

  constructor(props) {
    super(props);
    this.widgets = [];
    this.widgetRemoveListeners = [];
  }

  componentDidMount() {
    this.registerWidgetMessageListeners();
  }

  /**
   * This component should not update after the initial render
   *
   * @param {{}} nextProps
   * @param {{}} nextState
   * @return {boolean}
   */
  shouldComponentUpdate(nextProps, nextState) { // eslint-disable-line class-methods-use-this, no-unused-vars
    return false;
  }

  componentWillUnmount()  {
    this.widgets.forEach(this.unregisterWidget.bind(this));
  }

  /**
   * @param {Widget} widget
   * @param {String} eventName
   * @param {WidgetRequest} widgetMessage
   */
  onWidgetMouseEventMessage(widget, eventName, widgetMessage) { /* empty on purpose, can be overriden by subclasses*/ } // eslint-disable-line no-unused-vars

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

  /**
   * @param {WidgetConfiguration} widgetConfiguration
   * @param {{data: Object}}  event
   */
  onWidgetMessageReceive(widgetConfiguration, event) {
    // find the widget
    const initiatorWidget = find(this.widgets, widget => widget.configuration === widgetConfiguration);
    if (!initiatorWidget) {
      throw new Error('failed to dispatch incoming message: unrecognized widget');
    }

    const { parseIncomingWidgetMessageJS } = this.props;
    const widgetMessage = parseIncomingWidgetMessageJS(event.data);

    if (!widgetMessage) {
      throw new Error('failed to dispatch incoming message: could not parse widget message');
    }

    const { eventName } = event.data;
    if (eventName === ContainerEvents.EVENT_WINDOW_MOUSEEVENT) {
      this.onWidgetMouseEventMessage(initiatorWidget, eventName, widgetMessage);
    } else if (eventName)  {
      this.onWidgetAppMessage(initiatorWidget, eventName, widgetMessage);
    } else {
      throw new Error('failed to dispatch incoming message: unrecognized event name');
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
   * @param {WidgetConfiguration} widgetConfiguration
   * @param {{data: Object}} event
   */
  onWidgetInit(widgetConfiguration, event) { // eslint-disable-line no-unused-vars
    const { addWidgetEventListener } = this.props;
    const widget = new Widget({
      configuration: widgetConfiguration,
      windowId:      `urn:deskpro:widget?widgetId=${widgetConfiguration.id}`
    });

    const removeListeners = [
      addWidgetEventListener(widget.id, this.onWidgetMessageSend.bind(this)),
      addWidgetEventListener(`subscribe.${widget.id}`, this.onWidgetEventSubscribe.bind(this))
    ];

    this.registerWidget(widget, removeListeners);

    const { context } = this.props;
    return {
      instanceProps: widgetConfiguration.appConfig.toWidgetProps().toJS(),
      contextProps:  context.widgetProps.toJS()
    };
  }

  registerWidgetMessageListeners()  {
    /**
     * @param {WidgetConfiguration} widgetConfiguration
     */
    for (const widgetConfiguration of this.props.widgetsConfigList) {
      postRobot.once(
        `urn:deskpro:apps.widget.onready?widgetId=${widgetConfiguration.id}`,
        this.onWidgetInit.bind(this, widgetConfiguration)
      );

      postRobot.on(
        `urn:deskpro:apps.widget.event?widgetId=${widgetConfiguration.id}`,
        this.onWidgetMessageReceive.bind(this, widgetConfiguration)
      );
    }
  }

  /**
   * @param {Widget} widget
   * @param {Array<function>} removeListeners
   */
  registerWidget(widget, removeListeners) {
    // store the widget
    this.widgets.push(widget);
    // index the widget remove listeners
    const widgetRemoveListeners = removeListeners.map(removeListener => ({ widget, removeListener }));
    this.widgetRemoveListeners = this.widgetRemoveListeners.concat(widgetRemoveListeners);
  }

  /**
   * @param {Widget} widget
   */
  unregisterWidget(widget) {
    //
    // remove widget
    removeMatching(this.widgets, aWidget => aWidget === widget);

    // remove listeners
    const invokeRemoveListener = ({ removeListener }) => removeListener();
    removeMatching(this.widgetRemoveListeners, listener => listener.widget === widget).forEach(invokeRemoveListener);
  }

  /**
   * Renders all the apps
   *
   * @returns {XML}
   */
  renderWidgets() {
    const components = this.props.widgetsConfigList.map(DeskproAppContainer.mapWidgetConfigurationToReactComponent);
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
      return this.renderWidgets();
    }

    return DeskproAppContainer.renderEmpty();
  }


}

export { DeskproAppContainer };
