import React, { PropTypes } from 'react';
import * as xcomponent from 'xcomponent/src';
import uuid from 'node-uuid';
import AppEventDispatcher from '../Services/AppEventDispatcher'

/**
 * This container represents the integration point between an external app and deskpro.
 * It handles a list of apps within the same app context, managing their lifecycle and communication, behaving in this
 * respect as a router (routing and transforming deskpro events / messages to app components)
 */
class DeskproAppContainer extends React.Component {

  static propTypes = {
    widgets: PropTypes.array.isRequired
    , context: PropTypes.object.isRequired
    , dispatcher: PropTypes.object.isRequired
    , targetType: PropTypes.string.isRequired
  };

  constructor(props) {
    super(props);
    this.components = new Map();
    this.deskproEventDispatcher = new AppEventDispatcher();
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
    const components = widgets.map( widget => this.createReactElement(widget));

    return (<div> {components} </div> );
  }

  /**
   * @param {WidgetConfiguration} widgetConfig
   * @return {ReactElement}
   */
  createReactElement = (widgetConfig) =>
  {
    const reactClass = xcomponent.create(widgetConfig.xcomponentConfig).react;
    const reactProps = {
      key: uuid(),
      onEnter: DeskproAppContainer.createOnXComponentEnterListener(this),
      app: widgetConfig.appConfig.id,
      onDpMessage: (eventName, message) => this.onXComponentMessage(eventName, message)
    };

    return React.createElement(reactClass, reactProps);
  };

  dispatchGetState = (app, state) =>
  {
    const parentComponent = this.components.get(app);
    this.deskproEventDispatcher.dispatchOnGetState(state, parentComponent);
  };

  dispatchGetAllState = (app, state) =>
  {
    const parentComponent = this.components.get(app);
    this.deskproEventDispatcher.dispatchOnGetAllState(state, parentComponent);
  };

  dispatchSaveState = (app, state) =>
  {
    const parentComponent = this.components.get(app);
    this.deskproEventDispatcher.dispatchOnSaveState(state, parentComponent);
  };

  /**
   * @param {String} eventName
   * @param {Object} message
   */
  onXComponentMessage = (eventName, message) =>
  {
    const {app, args} = message;
    const parentComponent = this.components.get(app);
    const { dispatcher } = this.props;
    let callback, state;

    switch (eventName)
    {
      case 'context-init':
        const { context } = this.props;
        this.deskproEventDispatcher.dispatchOnContextInit(context, parentComponent);
        break;
      case 'get-all-state':
        callback = state => this.dispatchGetAllState(app, state);
        dispatcher.dispatchFindAllAppState(app, callback);
        break;
      case 'get-state':
        [ state ] = args;
        const { name, scope } = state;
        callback = state => this.dispatchGetState(app, state);
        dispatcher.dispatchGetAppState(app, name, scope, callback);
        break;
      case 'save-state':
        [ state ] = args;
        callback = state => this.dispatchSaveState(app, state);
        dispatcher.dispatchSaveState(app, state, callback);
      break;
    }
  };

  /**
   * Handler for the onEnter event sent by the parentComponent of an xcomponent
   *
   * @param {ParentComponent} parentComponent
   */
  onXComponentEnter = (parentComponent) =>
  {
    const { app } = parentComponent.props;
    this.components.set(app, parentComponent);

    // we should send which app has mounted
    const { targetType, dispatcher, context } = this.props;
    dispatcher.dispatchAppMounted(targetType);
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
