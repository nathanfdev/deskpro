import PropTypes from 'prop-types';
import React from 'react';
import { Widget } from '../Domain/Widget';

import { ContainerEvents } from './ContainerEvents';
import { EventProvider } from './EventProvider';
import { WidgetContainer } from './WidgetContainer';

/**
 * @param {Array} list
 * @param {function} filter
 * @return {null|*}
 */
const find = (list, filter) => {
  const found = list.filter(filter);
  return found.length === 1 ? found[0] : null;
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

  state = {
    getEvent:  () => ({}),
    initiated: [], // note: is also changed without triggering a render
    listeners: []  // note: is also changed without triggering a render
  };

  componentWillUnmount()  {
    this.unregisterAllWidgets();
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

  onWidgetEventSubscribe = (widgetConfiguration, eventName, eventSubscriber) => {
    const widget = find(this.widgets, aWidget => aWidget.configuration === widgetConfiguration);
    if (!widget) { return null; }

    const unsubscribe = eventSubscriber(eventName, widget);
    this.listeners.push({ widget, removeListener: unsubscribe });
    return null;
  };

  /**
   * @param {WidgetConfiguration} widgetConfiguration
   * @param {{data: Object}}  event
   */
  onWidgetMessageReceive = (widgetConfiguration, event) =>  {
    // find the widget
    const configuration = find(this.props.widgetsConfigList, aConfiguration => aConfiguration === widgetConfiguration);
    if (!configuration) {
      throw new Error('failed to dispatch incoming message: unrecognized widget');
    }

    const { parseIncomingWidgetMessageJS } = this.props;
    const widgetMessage = parseIncomingWidgetMessageJS(event.data);

    if (!widgetMessage) {
      throw new Error('failed to dispatch incoming message: could not parse widget message');
    }

    const from = new Widget({
      configuration: widgetConfiguration,
      windowId:      widgetConfiguration.canonicId
    });

    const { eventName } = event.data;
    if (eventName === ContainerEvents.EVENT_WINDOW_MOUSEEVENT) {
      this.onWidgetMouseEventMessage(from, eventName, widgetMessage);
    } else if (eventName)  {
      this.onWidgetAppMessage(from, eventName, widgetMessage);
    } else {
      throw new Error('failed to dispatch incoming message: unrecognized event name');
    }
  };

  /**
   * @param {WidgetConfiguration} widgetConfiguration
   * @param {String} eventName
   * @param {WidgetRequest|WidgetResponse} widgetMessage
   */
  onWidgetMessageSend = (widgetConfiguration, eventName, widgetMessage) =>  {
    const getEvent = (aConfiguration) => {
      if (widgetConfiguration === aConfiguration) {
        return { name: eventName, message: widgetMessage };
      }

      return { name: null, message: null };
    };

    this.setState({ getEvent });
  };

  /**
   * @param {WidgetConfiguration} widgetConfiguration
   * @param {{data: Object}} event
   */
  onWidgetInit = (widgetConfiguration, event) => { // eslint-disable-line no-unused-vars
    const { context } = this.props;

    const isInitiated = find(this.state.initiated, initiated => initiated.widgetConfiguration === widgetConfiguration);
    if (isInitiated) {
      return {
        instanceProps: widgetConfiguration.appConfig.toWidgetProps().toJS(),
        contextProps:  context.widgetProps.toJS()
      };
    }

    const { addWidgetEventListener } = this.props;

    const listeners = [
      addWidgetEventListener(widgetConfiguration.id, this.onWidgetMessageSend),
      addWidgetEventListener(`subscribe.${widgetConfiguration.id}`, this.onWidgetEventSubscribe)
    ];
    // no need to trigger a refresh for now
    Array.prototype.push.apply(this.state.listeners, listeners.map(removeListener => ({ widgetConfiguration, removeListener })));

    // no need to trigger a refresh for now
    this.state.initiated.push({ widgetConfiguration });

    return {
      instanceProps: widgetConfiguration.appConfig.toWidgetProps().toJS(),
      contextProps:  context.widgetProps.toJS()
    };
  };

  getEventProviders = () => ({

    onReady: new EventProvider({
      urn:     'urn:deskpro:apps.widget.onready',
      handler: this.onWidgetInit
    }),

    onEvent: new EventProvider({
      urn:     'urn:deskpro:apps.widget.event',
      handler: this.onWidgetMessageReceive
    })

  });

  unregisterAllWidgets()  {
    const { listeners } = this.state;
    this.state.initiated = [];

    for (const listener of listeners) {
      const { removeListener } = listener;
      removeListener();
    }
  }

  /**
   * @param {WidgetConfiguration} config
   */
  unregisterWidget = (config) =>  {
    const { initiated, listeners } = this.state;

    for (let i = 0; i < initiated.length; i++) {
      if (initiated[i] === config) {
        initiated.splice(i, 1);
        break;
      }
    }

    this.state.listeners = listeners.filter(
      ({ widgetConfiguration, removeListener }) => {
        if (widgetConfiguration === config) {
          removeListener();
          return false;
        }
        return true;
      }
    );
  };

  renderWidget = configuration =>  (<WidgetContainer
    configuration={configuration}
    getEvent={this.state.getEvent}
    getEventProviders={this.getEventProviders}
    unregister={this.unregisterWidget}
  />);

  /**
   * Renders the container and all the apps
   *
   * @returns {XML}
   */
  render() {
    const { widgetsConfigList } = this.props;
    if (widgetsConfigList && widgetsConfigList.length > 0) {
      return (<div>
        {this.props.widgetsConfigList.map(this.renderWidget)}
      </div>);
    }

    return (<div />);
  }


}

export { DeskproAppContainer };
