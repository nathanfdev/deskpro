import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import AgentTopBar from './Modules/TopBar/AgentTopBar';
import { api, setApi, loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL';
import { repositoriesConfig } from 'DeskPRO/Bundle/AgentBundle/DAL/config';
import { createStore, applyMiddleware, compose } from 'redux';
import { combineReducerHierarchy } from 'Ampliflux';
import AgentReducers from './AgentApp_Reducers';
import AppReducers from '../AppBundle/AppApp_Reducers';
import * as ampMiddleware from 'Ampliflux/middleware';
import { preloadData } from './Modules/Application/Actions/bootstrapActions';

export class AgentLegacyApp {
  run() {
    window.$(document).on('ready', () => this.start());
  }

  start() {
    if (typeof window.DeskPRO_Window === 'undefined') {
      setTimeout(this.start.bind(this), 100);
    } else {
      this.store = AgentLegacyApp.createStore();
      this.store.dispatch(preloadData());
      this.renderPiece(AgentTopBar);
    }
  }

  renderPiece(piece) {
    const element = React.createElement(piece, { store: this.store });
    let elementPlace;
    if (element.type.WrappedComponent) {
      elementPlace = element.type.WrappedComponent.name;
    } else {
      elementPlace = element.type.name;
    }

    elementPlace = elementPlace.replace(
      /([A-Z])/g,
      ($1) => `_${$1.toLowerCase()}`
    );

    const node = document.getElementById(`react_dp${elementPlace}`);
    // console.log(`react_dp${elementPlace}`);
    // const node = document.getElementById('dp_header');
    if (node) {
      ReactDOM.render(element, node);
    }
  }

  static createStore(initialState = {}) {
    // Bootstrap API and DAL
    setApi(api);
    loadRepositoriesConfig(repositoriesConfig);

    // This builder calls compile on old-style reducers
    // created via the Reducer class
    const legacyReducerBuilder = function (reducer) {
      if (reducer.isAmplifluxReducer) {
        const rInst = new reducer();
        return rInst.compile();
      }

      return reducer;
    };

    const reducer = combineReducerHierarchy(Object.assign({}, AgentReducers, AppReducers), legacyReducerBuilder);
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
}
