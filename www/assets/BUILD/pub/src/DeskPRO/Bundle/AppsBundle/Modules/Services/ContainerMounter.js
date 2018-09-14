import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';

import { DeskproAppContainer, AppsColumnContainer } from '../Components';
import { ContainerConfiguration } from './ContainerConfiguration';
import getSidebarState from './sidebarState';

/**
 * @return {DeskPRO.MessageBroker}
 */
function sendMessageLegacyMessageBroker(name, data) {
  return window.DeskPRO_Window.getMessageBroker().sendMessage(name, data);
}

/**
 * This class mounts the react container components
 */
class ContainerMounter {
  /**
   * @param reduxStore
   * @param {AppsRegistry} appRegistry
   */
  constructor(reduxStore, appRegistry)  {
    this.reduxStore = reduxStore;
    this.appRegistry = appRegistry;
  }

  /**
   * @param {ContainerConfiguration} configuration
   * @return {Array.<WidgetConfiguration>}
   */
  getWidgetConfigForContainer(configuration)  {
    const targetType = configuration.targetType;

    const { appRegistry } = this;
    return appRegistry.getWidgetConfigByTargetType(targetType);
  }

  /**
   * @param {Context} context
   * @param {Object} domNode
   */
  unmountAt(context, domNode) { // eslint-disable-line no-unused-vars, class-methods-use-this
    if (ReactDOM.unmountComponentAtNode(domNode)) {
      console.info(`app container unmounted from location ${context.locationId}`);
    } else {
      console.warn(`failed to unmount app container from location ${context.locationId}`);
    }
  }

  /**
   * @param {Context} context
   * @param {Object} domNode
   * @return {integer}
   */
  mountAt(context, domNode) {
    const configuration = ContainerConfiguration.fromDOM(domNode);
    const widgetsConfigList = this.getWidgetConfigForContainer(configuration);
    const props = { context, widgetsConfigList };

    let reactElement = null;
    const { renderType: renderStrategy } = configuration;
    if (renderStrategy === 'inplace') {
      reactElement = this.renderInplace(domNode, configuration, props);
    } else if (renderStrategy === 'apps-column') {
      reactElement = this.renderAppsColumn(domNode, configuration, props);
    }

    if (!reactElement) {
      throw new Error(`unknown render strategy: ${renderStrategy}`);
    }

    // TODO this is a temporary hack to prevent the sidebar appearing every time
    return props.widgetsConfigList.length;
  }

  /**
   * @param dom
   * @param {ContainerConfiguration} configuration
   * @param {Object} props
   * @return {XML}
   */
  renderInplace = (dom, configuration, props) => { // eslint-disable-line no-unused-vars
    const appContainer = React.createElement(DeskproAppContainer, props);
    const { reduxStore } = this;

    const reactElement = <Provider store={reduxStore}>{ appContainer }</Provider>;
    ReactDOM.render(reactElement, dom);

    return reactElement;
  };

  /**
   * @param dom
   * @param {ContainerConfiguration} configuration
   * @param {Object} props
   * @return {XML}
   */
  renderAppsColumn = (dom, configuration, props) => { // eslint-disable-line no-unused-vars
    const { reduxStore } = this;

    const container = React.createElement(AppsColumnContainer, { ...props, getSidebarState, sendMessageLegacyMessageBroker });
    const reactElement = <Provider store={reduxStore}>{ container }</Provider>;

    ReactDOM.render(reactElement, dom);
    return reactElement;
  };
}

export { ContainerMounter };
