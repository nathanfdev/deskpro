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
   * @param {Array<String>} validTargets
   */
  constructor(domSelector, appConfig, validTargets) {
    this.domSelector = domSelector;
    this.instanceConfig = appConfig;
    this.validTargets = validTargets;
  }

  /**
   * @param {string} targetType
   * @returns {Object}
   */
  getInstanceConfigForTarget = (targetType) =>
  {
    if (this.instanceConfig.hasOwnProperty(targetType)) {
      return this.instanceConfig[targetType];
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

    for (let i = 0; i < domNodeList.length; i++) {
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
    const config = this.getInstanceConfigForTarget(targetType);
    if (config) {
      return React.createElement(DeskproAppContainer, {target: targetType, appConfig: config, context: context});
    } else {
      throw new Error('asdadsad sa ' + targetType);
    }

    return null;
  };

}

export default DeskproAppContainerLoader;
