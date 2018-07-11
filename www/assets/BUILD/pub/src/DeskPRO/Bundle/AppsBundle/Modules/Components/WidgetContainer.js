import PropTypes from 'prop-types';
import React from 'react';

import * as postRobot from 'post-robot';

import { WidgetConfiguration } from '../Domain';
import { WidgetIframe } from './WidgetIframe';

export class WidgetContainer extends React.PureComponent {
  static propTypes = {
    configuration:     PropTypes.instanceOf(WidgetConfiguration),
    getEvent:          PropTypes.func,
    getEventProviders: PropTypes.func,
    unregister:        PropTypes.func
  };

  componentDidUpdate(prevProps)  { // eslint-disable-line no-unused-vars
    const { getEvent }      = this.props;
    const { name, message } = getEvent(this.props.configuration);

    if (name && message && this.window) {
      postRobot.send(this.window, name, message.toJS()).catch(e => console.error('message not send because of post-robot error ', e));
    }

    if (name && message && !this.window) {
      console.error('message not send because widget window is not ready ', message);
    }
  }

  componentWillUnmount()  {
    this.listeners.forEach(listener => listener.cancel());
    this.listeners = [];
    this.props.unregister(this.props.configuration);
  }

  onWindowReady = (wnd) =>  {
    this.window = wnd;
    this.releaseAll();
    this.registerAll();
  };

  onReady = (event) => {
    const { onReady } = this.props.getEventProviders();
    return this.handle(onReady, event);
  };


  onEvent = (event) => {
    const { onEvent } = this.props.getEventProviders();
    return this.handle(onEvent, event);
  };

  releaseAll()  {
    for (const listener of this.listeners) {
      listener.cancel();
    }
    this.listeners = [];
  }

  registerAll()  {
    const { /** @type {function} */ getEventProviders } = this.props;

    const { onReady, onEvent } = getEventProviders();
    this.register(onReady, this.onReady);
    this.register(onEvent, this.onEvent);
  }

  /**
   * @param {EventProvider} eventProvider
   * @param {function} handler
   */
  register(eventProvider, handler)  {
    const { /** @type {WidgetConfiguration} */ configuration } = this.props;
    const eventName = eventProvider.getName(configuration);
    const options = this.window ? { window: this.window } :  {};

    const listener = postRobot.on(eventName, options, handler);
    this.listeners.push(listener);
  }

  /**
   * @param {EventProvider} eventProvider
   * @param {*} event
   */
  handle(eventProvider, event)  {
    const { /** @type {WidgetConfiguration} */ configuration } = this.props;
    const handler = eventProvider.getHandler(configuration);
    return handler(configuration, event);
  }

  preRender()  {
    if (!this.registerAllRan) {
      this.registerAllRan = true;
      this.registerAll();
    }
  }

  window = null;

  listeners = [];

  registerAllRan = false;

  render()  {
    this.preRender();

    const { /** @type {WidgetConfiguration} */ configuration } = this.props;
    return (<WidgetIframe
      id={configuration.canonicId}
      url={configuration.getUrl()}
      onWindowReady={this.onWindowReady}

    />);
  }

}
