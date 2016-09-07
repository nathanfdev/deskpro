import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { createStore, applyMiddleware, compose } from 'redux';
import { Provider } from 'react-redux';
import { combineReducerHierarchy } from 'Ampliflux';
import * as ampMiddleware from 'Ampliflux/middleware';
import { IntlProvider } from 'react-intl';
import Immutable from 'immutable';
import { api, setApi, loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL';
import { repositoriesConfig } from 'DeskPRO/Bundle/AgentBundle/DAL/config';
import AgentReducers from './AgentApp_Reducers';
import AppReducers from '../AppBundle/AppApp_Reducers';
import { DpAppContainer } from './Modules/Application/Components/DpAppContainer';

window.Immutable = Immutable;
/**
 * ---------------------------------------------------------------------------------------------------------------------
 * It's here temporarily
 */
window.DP_LOCALE = 'en';
window.DP_LANG = {
  'feedback.nav.title':       'Feedback',
  'feedback.nav.tabs.status': 'Status'
};
// ---------------------------------------------------------------------------------------------------------------------

export class AgentApp {
  run() {
    document.addEventListener('DOMContentLoaded', () => this.start());
  }

  start() {
    /* global __DEV__ */
    window.DP_DEV_MODE = __DEV__;
    const store = AgentApp.createStore();

    ReactDOM.render(
      <div>
        <Provider store={store}>
          <IntlProvider locale={window.DP_LOCALE} messages={window.DP_LANG}>
            <DpAppContainer />
          </IntlProvider>
        </Provider>
      </div>,
      document.getElementById('deskpro_app_window')
    );
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
export default AgentApp;
