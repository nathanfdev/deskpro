import React, { PropTypes } from 'react';
import * as xcomponent from 'xcomponent/src';
import { connect } from 'react-redux';
import uuid from 'node-uuid';
import DeskproEventDispatcher from '../Services/DeskproEventDispatcher'
import {appMounted} from '../Actions/Actions'

function mapDispatchToProps(dispatch)
{
  return { dispatch }
}

/**
 * This container represents the integration point between an external app and deskpro.
 * It handles a list of apps within the same app context, managing their lifecycle and communication, behaving in this
 * respect as a router (routing and transforming deskpro events / messages to app components)
 */
@connect(null, mapDispatchToProps)
class DeskproAppContainer extends React.Component {

  static propTypes = {
    appConfig: PropTypes.array.isRequired
    , context: PropTypes.object.isRequired
    , dispatch: PropTypes.func.isRequired
    , target: PropTypes.string.isRequired
  };

  constructor(props) {
    super(props);
    this.components = [];
    this.deskproEventDispatcher = new DeskproEventDispatcher();
  }

  /**
   * Renders the container and all the apps
   *
   * @returns {XML}
   */
  render() {
    const {appConfig} = this.props;
    if (appConfig) {
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
    const {appConfig} = this.props;
    const components = appConfig.map((config) => this.createReactElement(config));

    return (<div> {components} </div> );
  }

  /**
   * @param {Object} config
   * @return {ReactElement}
   */
  createReactElement = (config) =>
  {
    //TODO: convert config to xconfig, for now assume isomporphism
    const reactClass = xcomponent.create(config).react;
    const reactProps = this.createReactElementProps();

    return React.createElement(reactClass, reactProps);
  };

  /**
   * @returns {{key, onEnter: Function}}
   */
  createReactElementProps = () =>
  {
    return {
      key: uuid(),
      onEnter: DeskproAppContainer.createOnXComponentEnterListener(this),
      onDpMessage: (eventName, message) => this.onXComponentMessage(eventName, message)
    };
  };

  /**
   * @param {object} message
   */
  onContextInit(message)
  {
    const iter = (parentComponent) => {
      this.deskproEventDispatcher.dispatchOnContextInit(message, parentComponent);
    };
    this.components.each(iter);
  }

  onXComponentMessage = (eventName, message) =>
  {
    switch (eventName)
    {
      case 'context-init':
        const { context } = this.props;
        console.log ('sending context', context);
        this.onContextInit(context);
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
    this.components.push(parentComponent);

    const { target, dispatch, context } = this.props;
    dispatch(appMounted(target, parentComponent));
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
