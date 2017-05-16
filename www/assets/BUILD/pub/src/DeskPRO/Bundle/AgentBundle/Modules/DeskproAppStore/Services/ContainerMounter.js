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

  createProps = (configuration, context) =>
  {
      const { appstoreDispatcher, appRegistry, widgetMessageRouter, widgetMessageBroker } = this;

      const targetType = configuration.targetType;
      const widgets = appRegistry.getWidgetConfigByTargetType(targetType);

      return { context, appstoreDispatcher, configuration, widgets, widgetMessageRouter, widgetMessageBroker } ;
  };


  /**
   * @param {Array} domNodeList
   * @param {Object} context
   * @return {*}
   */
  mount = (domNodeList, context) =>
  {
    // based on each container's rendering strategy, build a list of render maps for each dom node
    // a render map is map of dom nodes and their corresponding react elements
    const renderMaps = domNodeList.map(dom => {
      const configuration = ContainerConfiguration.fromDOM(dom);
      const props = this.createProps(configuration, context);

      if (configuration.renderType === 'inplace') {
        return this.createInPlaceReactElement(dom, configuration, props);
      }

      if (configuration.renderType === 'legacy-sidebar') {
        return this.createLegacySidebarReactElement(dom, configuration, props);
      }

      throw new Error('unknown render strategy');
    });

    //transform the list of maps into a list of entries for easier rendering
    const entries = renderMaps.reduce(function (acc, renderMap) {
        for (let entry of renderMap.entries()) { acc.push(entry); }
        return acc;
    }, []);

    const reactElementList = [];
    for (let i = 0; i < entries.length; i++) {
      const reactElement = entries[i][1];
      reactElementList.push(reactElement);
      ReactDOM.render(reactElement, entries[i][0]);
    }

    return reactElementList;
  };

  /**
   * @param dom
   * @param {ContainerConfiguration} config
   * @param {Object} props
   * @return {Map}
   */
  createInPlaceReactElement = (dom, config, props) => {
    const reactContainer = dom;
    const appContainer = React.createElement(DeskproAppContainer, props);
    const { reduxStore } = this;

    const reactElement = <Provider store={ reduxStore }>{ appContainer }</Provider>;

    const renderMap = new Map();
    renderMap.set(reactContainer, reactElement);
    return renderMap;
  };

  /**
   * @param dom
   * @param {ContainerConfiguration} config
   * @param {Object} props
   * @return {Map}
   */
  createLegacySidebarReactElement = (dom, config, props) => {
    const { reduxStore } = this;
    const reactContainer = LegacyAppSidebar.fromSelector(config.renderSidebarContainer).getContentRoot();

    const container = React.createElement(LegacySidebarContainer, props);
    const reactElement = <Provider store={ reduxStore }>{ container }</Provider>;

    const renderMap = new Map();
    renderMap.set(reactContainer, reactElement);
    return renderMap;
  };
}

export default ContainerMounter;
