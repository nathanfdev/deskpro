import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';

import { DeskproAppContainerProps, DeskproAppContainer, LegacySidebarContainer, LegacyAppSidebar, AppsColumnContainer } from '../Components';
import { ContainerConfiguration } from './ContainerConfiguration';

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
    const configuration = ContainerConfiguration.fromDOM(domNode);

    let mountRoot = null;
    const { renderType: renderStrategy } = configuration;
    if (renderStrategy === 'inplace') {
      mountRoot = domNode;
    } else if (renderStrategy === 'legacy-sidebar' || renderStrategy === 'apps-column') {
      mountRoot = LegacyAppSidebar.fromSelector(configuration.renderSidebarContainer).getContentRoot();
    }

    if (mountRoot) {
      ReactDOM.unmountComponentAtNode(mountRoot);
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
    }    else if (renderStrategy === 'legacy-sidebar') {
      reactElement = this.renderLegacySidebar(domNode, configuration, props);
    }

    if (!reactElement) {
      throw new Error(`unknown render strategy: ${renderStrategy}`);
    }

    // TODO this is a temporary hack to prevent the sidebar appearing every time
    return props.widgetsConfigList.length;
  }

  /**
   * @param dom
   * @param {ContainerConfiguration} config
   * @param {Object} props
   * @return {XML}
   */
  renderInplace = (dom, config, props) => {
    const reactContainer = dom;
    const appContainer = React.createElement(DeskproAppContainer, props);
    const { reduxStore } = this;

    const reactElement = <Provider store={reduxStore}>{ appContainer }</Provider>;
    ReactDOM.render(reactElement, reactContainer);


    return reactElement;
  };

  /**
   * @param dom
   * @param {ContainerConfiguration} configuration
   * @param {Object} props
   * @return {XML}
   */
  renderAppsColumn = (dom, configuration, props) => {
    const { reduxStore } = this;
    const reactContainer = LegacyAppSidebar.fromSelector(configuration.renderSidebarContainer).getContentRoot();

    const container = React.createElement(AppsColumnContainer, { ...props, configuration, sendMessageLegacyMessageBroker });
    const reactElement = <Provider store={reduxStore}>{ container }</Provider>;

    ReactDOM.render(reactElement, reactContainer);
    return reactElement;
  };

  /**
   * @param dom
   * @param {ContainerConfiguration} configuration
   * @param {Object} props
   * @return {XML}
   */
  renderLegacySidebar = (dom, configuration, props) => {
    const { reduxStore } = this;
    const reactContainer = LegacyAppSidebar.fromSelector(configuration.renderSidebarContainer).getContentRoot();

    const container = React.createElement(LegacySidebarContainer, { ...props, configuration });
    const reactElement = <Provider store={reduxStore}>{ container }</Provider>;

    ReactDOM.render(reactElement, reactContainer);
    return reactElement;
  };
}

export { ContainerMounter };
