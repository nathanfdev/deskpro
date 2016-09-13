import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware, compose } from 'redux';
import { combineReducerHierarchy } from 'Ampliflux';
import * as ampMiddleware from 'Ampliflux/middleware';
import { api, setApi, loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL';
import { repositoriesConfig } from 'DeskPRO/Bundle/AgentBundle/DAL/config';
import AgentTopBar from './Modules/TopBar/Components/AgentTopBar';
import { SideBarContainer } from './Modules/SideBar/Components/SideBar';
import { AgentList } from './Modules/Agent/Components/AgentList';
import { AgentOnboardingContainer }  from './Modules/Onboarding/Components/AgentOnboarding';
import AgentReducers from './AgentApp_Reducers';
import AppReducers from '../AppBundle/AppApp_Reducers';
import { preloadData } from './Modules/Application/Actions/bootstrapActions';

class AgentLegacyApp {

  rendered = [];
  store;

  run() {
    this.store = AgentLegacyApp.createStore();
    this.store.dispatch(preloadData());
    window.$(document).on('ready', () => this.start());
  }

  start() {
    if (typeof window.DeskPRO_Window === 'undefined'
      || !this.store.getState().Application.bootstrap.get('isBootstrapped')) {
      setTimeout(this.start.bind(this), 100);
    } else {
      this.renderPiece(AgentTopBar, AgentTopBar.getType());
      this.renderPiece(AgentList, AgentList.getType());
      this.renderPiece(SideBarContainer, SideBarContainer.getType());
      this.renderPiece(AgentOnboardingContainer, AgentOnboardingContainer.getType());
    }
  }

  renderPiece(piece, piecePlace) {
    if (this.rendered[piecePlace]) {
      return;
    }

    const element = React.createElement(piece, { store: this.store });
    const elementPlace = piecePlace.replace(/([A-Z])/g, ($1) => `_${$1.toLowerCase()}`);
    const node = document.getElementById(`react_dp${elementPlace}`);

    if (node) {
      this.rendered[piecePlace] = true;
      ReactDOM.render(<Provider store={this.store}>{element}</Provider>, node);
      return;
    }
    // this is just a stub. Possibly we just need some event listener or so to handle element rendering when
    // some page component was just loaded.
    // maybe we could use something like angular directive or so.
    // it's not good when there are elements for different pages.
    // Or, we may just call this method when it's needed.
    setTimeout(() => this.renderPiece(piece, piecePlace), 1000);
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
export default AgentLegacyApp;
