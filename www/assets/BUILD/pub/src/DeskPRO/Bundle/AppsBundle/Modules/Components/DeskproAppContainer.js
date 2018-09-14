import PropTypes from 'prop-types';
import React from 'react';
import { Widget } from '../Domain';

import { ContainerEvents } from './ContainerEvents';
import { EventProvider } from './EventProvider';
import { WidgetContainerListEmpty } from './WidgetContainerListEmpty';
import { WidgetContainerList } from './WidgetContainerList';

import { receiveMessage, receiveSubscription, registerOutgoingMessageListener } from '../WidgetMessage';

/**
 * @param {Array} list
 * @param {function} filter
 * @return {null|*}
 */
const find = (list, filter) => {
  const found = list.filter(filter);
  return found.length === 1 ? found[0] : null;
};


function defaultRenderer({ widgets, getEvent, getEventProviders, unregister }) {
  if (widgets && widgets.length > 0) {
    return (
      <WidgetContainerList
        widgets={widgets}
        getEvent={getEvent}
        getEventProviders={getEventProviders}
        unregister={unregister}
      />
    );
  }
  return (<WidgetContainerListEmpty />);
}
defaultRenderer.propTypes = {
  widgets:           PropTypes.array.isRequired,
  getEvent:          PropTypes.func.isRequired,
  getEventProviders: PropTypes.func.isRequired,
  unregister:        PropTypes.func.isRequired,
};


/* eslint class-methods-use-this: ["error", { "exceptMethods": ["onWidgetMouseEventMessage"] }] */

/**
 * This container represents the integration point between an external app and deskpro.
 * It handles a list of apps within the same app context, managing their lifecycle and communication, behaving in this
 * respect as a router (routing and transforming deskpro events / messages to app components)
 */
class DeskproAppContainer extends React.Component {

  static propTypes = {
    widgetsConfigList: PropTypes.array.isRequired,
    context:           PropTypes.object.isRequired,

    registerOutgoingMessageListener: PropTypes.func,
    receiveMessage:                  PropTypes.func,
    receiveSubscription:             PropTypes.func,
    children:                        PropTypes.func
  };

  static defaultProps = {
    registerOutgoingMessageListener,
    receiveMessage,
    receiveSubscription,
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
   * @param {{data: Object}} event
   */
  onWidgetMouseEventMessage(widget, eventName, event) { /* empty on purpose, can be overriden by subclasses*/ } // eslint-disable-line no-unused-vars


  // generic widget message handlers

  /**
   * @param {WidgetConfiguration} widgetConfiguration
   * @param {{data: Object}} event
   * @return {null}
   */
  onWidgetEventSubscribe(widgetConfiguration, event)  {
    const widget = new Widget({
      configuration: widgetConfiguration,
      windowId:      widgetConfiguration.canonicId
    });
    const unsubscribers = this.props.receiveSubscription(widget, event, this.props.context);

    Object.keys(unsubscribers).forEach((key) => {
      this.state.listeners.push({
        widgetConfiguration,
        removeListener: unsubscribers[key]
      });
    });
  }

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

    const from = new Widget({
      configuration: widgetConfiguration,
      windowId:      widgetConfiguration.canonicId
    });

    // implementation leak
    const { eventName } = event.data;

    try {
      if (eventName === ContainerEvents.EVENT_WINDOW_MOUSEEVENT) {
        this.onWidgetMouseEventMessage(from, eventName, event.data);
      } else if (eventName === ContainerEvents.EVENT_SUBSCRIBE) {
        this.onWidgetEventSubscribe(widgetConfiguration, event);
      } else {
        this.props.receiveMessage(from, event);
      }
    } catch (e) {
      console.error('failed to process a widget message ', e);
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

    const from = new Widget({
      configuration: widgetConfiguration,
      windowId:      widgetConfiguration.canonicId
    });

    const listeners = [
      this.props.registerOutgoingMessageListener(from, this.onWidgetMessageSend),
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

  /**
   * Renders the container and all the apps
   *
   * @returns {XML}
   */
  render() {
    const { widgetsConfigList } = this.props;

    if (typeof this.props.children === 'function') {
      return this.props.children({
        getEvent:          this.state.getEvent,
        getEventProviders: this.getEventProviders,
        unregister:        this.unregisterWidget
      });
    }

    return defaultRenderer({
      widgets:           widgetsConfigList,
      getEvent:          this.state.getEvent,
      getEventProviders: this.getEventProviders,
      unregister:        this.unregisterWidget
    });
  }


}

export { DeskproAppContainer };
