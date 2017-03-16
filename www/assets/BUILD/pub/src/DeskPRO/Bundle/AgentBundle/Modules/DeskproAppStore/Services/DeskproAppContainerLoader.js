import React from 'react';
import ReactDOM from 'react-dom';
import DeskproAppContainer from '../Components/DeskproAppContainer';
import ContainerDOMNodeSelector from './ContainerDOMNodeSelector';

import { Provider } from 'react-redux';


class DeskproAppContainerLoader
{
  /**
   * @param {ContainerDOMNodeSelector} domSelector
   * @param {Array<String>} validTargets
   * @param containerPropsFactory
   */
  constructor(domSelector, validTargets, containerPropsFactory) {
    this.domSelector = domSelector;
    this.validTargets = validTargets;
    this.containerPropsFactory = containerPropsFactory;
  }

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
    const props = this.containerPropsFactory(targetType, context);
    return React.createElement(DeskproAppContainer, props);
  };

}

export default DeskproAppContainerLoader;
