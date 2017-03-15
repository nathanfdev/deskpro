import React from 'react';
import ReactDOM from 'react-dom';
import DeskproAppContainer from '../Components/DeskproAppContainer';
import ContainerDOMNodeSelector from './ContainerDOMNodeSelector';

import { Provider } from 'react-redux';


class DeskproAppContainerLoader
{
  /**
   * @param {ContainerDOMNodeSelector} domSelector
   * @param {Object} appConfig
   */
  constructor(domSelector, appConfig) {
    this.domSelector = domSelector;
    this.appConfig = appConfig;
    this.validTargets = ['top-bar', 'ticket-sidebar'];
  }

  /**
   * @param {string} targetType
   * @returns {Object}
   */
  getAppConfigForTarget = (targetType) =>
  {
    if (this.appConfig.hasOwnProperty(targetType)) {
      return this.appConfig[targetType];
    }

    return null;
  };

  findContainerDOMNodeList = (list) =>
  {
    const acceptorFilter = this.domSelector.createAcceptorFromTargetType(this.validTargets);
    return this.domSelector.filterAll(list, acceptorFilter);
  };

  /**
   * @param {Array} domNodeList
   * @param {Object} context
   * @param {Object} store
   * @return {*}
   */
  load = (domNodeList, context, store) =>
  {
    const reactElementList = domNodeList.map((dom) => {
      const target = this.domSelector.type(dom);
      const reactElement = this.createReactContainer(target, context);
      return <Provider store={ store }>{ reactElement }</Provider>;
    });

    for (let i = 0; i < domNodeList.size; i++) {
      ReactDOM.render(reactElementList[i], domNodeList[i]);
    }

    return reactElementList;
  };

  /**
   * @param {String} targetType
   * @param {Object} context
   * @returns {null}
   */
  createReactContainer = (targetType, context) =>
  {
    const config = this.getAppConfigForTarget(targetType);
    if (config) {
      return React.createElement(DeskproAppContainer, {target: targetType, appConfig: config, context: context});
    }

    return null;
  };

}

export default DeskproAppContainerLoader;
