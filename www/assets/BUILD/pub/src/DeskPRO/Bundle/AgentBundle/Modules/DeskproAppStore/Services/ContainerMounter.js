import React from 'react';
import ReactDOM from 'react-dom';
import DeskproAppContainer from '../Components/DeskproAppContainer';
import LegacySidebarContainer from '../Components/LegacySidebarContainer';
import LegacyAppSidebar from '../Components/LegacyAppSidebar';

import ContainerConfiguration from './ContainerConfiguration';
import { Provider } from 'react-redux';

/**
 * This class mounts the react container components
 */
class ContainerMounter
{
  /**
   * @param reduxStore
   * @param {ReduxActionDispatcher} appstoreDispatcher
   * @param {Function} widgetMessageRouter
   * @param {Function} widgetMessageBroker
   * @param {DeskproAppRegistry} appRegistry
   */
  constructor(reduxStore, appstoreDispatcher, widgetMessageRouter, widgetMessageBroker, appRegistry)
  {
    this.reduxStore = reduxStore;
    this.appstoreDispatcher = appstoreDispatcher;
    this.widgetMessageRouter = widgetMessageRouter;
    this.widgetMessageBroker = widgetMessageBroker;
    this.appRegistry = appRegistry;
  }

  /**
   * @param {ContainerConfiguration} configuration
   * @param {Context} context
   * @return {{context: *, appstoreDispatcher: ContainerMounter.appstoreDispatcher, configuration: *, widgets: Array.<WidgetConfiguration>, widgetMessageRouter: ContainerMounter.widgetMessageRouter, widgetMessageBroker: ContainerMounter.widgetMessageBroker}}
   */
  createProps = (configuration, context) =>
  {
      const { appstoreDispatcher, appRegistry, widgetMessageRouter, widgetMessageBroker } = this;

      const targetType = configuration.targetType;
      const widgets = appRegistry.getWidgetConfigByTargetType(targetType);

      return { context, appstoreDispatcher, configuration, widgets, widgetMessageRouter, widgetMessageBroker } ;
  };

  /**
   * @param {Context} context
   * @param {Object} domNode
   * @return {*}
   */
  mountAt = (context, domNode) =>
  {
    const configuration = ContainerConfiguration.fromDOM(domNode);
    const props = this.createProps(configuration, context);

    let reactElement = null;
    const { renderType: renderStrategy } = configuration;
    if (renderStrategy === 'inplace') {
      reactElement = this.renderInplace(domNode, configuration, props);
    } else if (renderStrategy === 'legacy-sidebar') {
      reactElement = this.renderLegacySidebar(domNode, configuration, props);
    }

    if (!reactElement) {
      throw new Error(`unknown render strategy: ${renderStrategy}`);
    }

    ReactDOM.render(reactElement, domNode);
  };

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

    const reactElement = <Provider store={ reduxStore }>{ appContainer }</Provider>;
    ReactDOM.render(reactElement, reactContainer);

    return reactElement;
  };

  /**
   * @param dom
   * @param {ContainerConfiguration} config
   * @param {Object} props
   * @return {XML}
   */
  renderLegacySidebar = (dom, config, props) => {
    const { reduxStore } = this;
    const reactContainer = LegacyAppSidebar.fromSelector(config.renderSidebarContainer).getContentRoot();

    const container = React.createElement(LegacySidebarContainer, props);
    const reactElement = <Provider store={ reduxStore }>{ container }</Provider>;

    ReactDOM.render(reactElement, reactContainer);
    return reactElement;
  };
}

export default ContainerMounter;
