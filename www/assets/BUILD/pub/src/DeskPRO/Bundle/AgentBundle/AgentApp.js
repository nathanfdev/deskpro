import 'babel-polyfill';
import jQuery from 'jquery';
import React from 'react';
import ReactDOM from 'react-dom';
import { createStore, applyMiddleware, compose } from 'redux';
import { Provider } from 'react-redux';
import { combineReducerHierarchy } from 'Ampliflux';
import * as ampMiddleware from 'Ampliflux/middleware';
import AgentReducers from './AgentApp_Reducers';
import AppReducers from '../AppBundle/AppApp_Reducers';
import { DpAppContainer } from './Modules/Application/Components/DpAppContainer';
import { IntlProvider } from 'react-intl';
import Immutable from 'immutable';
window.Immutable = Immutable;
import { setApi, loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL';
import { repositoriesConfig } from 'DeskPRO/Bundle/AgentBundle/DAL/config';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

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
    jQuery(document).on('ready', () => this.start());
  }

  start() {
    window.DP_DEV_MODE = __DEV__;

    setApi(api);
    loadRepositoriesConfig(repositoriesConfig);
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

  static createStore() {
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

    return makeStore(reducer, {}, window.devToolsExtension ? window.devToolsExtension() : f => f);
  }
}
