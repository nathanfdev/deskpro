import 'babel-polyfill';
import React, { PropTypes } from 'react';
import { createStore, applyMiddleware, compose } from 'redux';
import { combineReducerHierarchy } from 'Ampliflux';
import * as ampMiddleware from 'Ampliflux/middleware';
import Isvg from 'react-inlinesvg';
import { api, setApi, loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL';
import logoSvg from 'DeskPRO/Bundle/DemoBundle/Resources/img/logo.svg';
import { repositoriesConfig } from './DAL/config';
import AdminReducers from './DemoApp_Reducers';
import AppReducers from '../AppBundle/AppApp_Reducers';

export class DemoApp extends React.Component {
  static propTypes = {
    children: PropTypes.node
  }

  static createStore(initialState = {}) {
    // Bootstrap API and DAL
    setApi(api);
    loadRepositoriesConfig(repositoriesConfig);

    // This builder calls compile on old-style reducers
    // created via the Reducer class
    const legacyReducerBuilder = (reducer) => {
      if (reducer.isAmplifluxReducer) {
        const rInst = new reducer();
        return rInst.compile();
      }

      return reducer;
    };

    const reducer = combineReducerHierarchy(Object.assign({}, AdminReducers, AppReducers), legacyReducerBuilder);
    const middleware = applyMiddleware(
      ampMiddleware.timerMiddleware('startTime'),
      ampMiddleware.intervalMiddleware,
      ampMiddleware.timeoutMiddleware,
      ampMiddleware.actionThunkMiddleware,
      ampMiddleware.redispatchDsaPayload,
      ampMiddleware.guidMiddleware,
      ampMiddleware.promiseMiddleware,
      ampMiddleware.loggerMiddleware
    );
    const makeStore = compose(middleware)(createStore);

    return makeStore(reducer, initialState, window.devToolsExtension ? window.devToolsExtension() : f => f);
  }

  componentWillMount() {
    this.start();
    document.addEventListener('DOMContentLoaded', () => this.start());
  }

  start() {
    /* global __DEV__ */
    window.DP_DEV_MODE = __DEV__;
    this.store = DemoApp.createStore();
  }

  render = () =>
    <div className="container">
      <div className="logo">
        <Isvg src={logoSvg} />
      </div>
      {this.props.children}
    </div>
}
export default DemoApp;
