import 'babel-polyfill';
import jQuery from 'jquery';
import React from 'react';
import ReactDOM from 'react-dom';
import { createStore, applyMiddleware, compose } from 'redux';
import { Provider } from 'react-redux';
import { combineReducerHierarchy } from 'Ampliflux';
import * as ampMiddleware from 'Ampliflux/middleware';
import AppReducers from './AgentApp_Reducers.js';
import { DpAppContainer } from './Modules/Application/Components/DpAppContainer';
import { IntlProvider } from 'react-intl';
import Immutable from 'immutable';
window.Immutable = Immutable;
import { loadRepositoriesConfig } from 'DeskPRO/Bundle/AppBundle/DAL/index';
import { repositoriesConfig } from 'DeskPRO/Bundle/AgentBundle/DAL/config';

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * It's here temporarily
 */
window.DP_LOCALE = 'en';
window.DP_LANG = {
  'feedback.nav.title': 'Feedback',
  'feedback.nav.tabs.status': 'Status'
};
// ---------------------------------------------------------------------------------------------------------------------

export class AgentApp {
  run() {
    jQuery(document).on('ready', () => this.start());
  }

  start() {
    window.DP_ENABLE_ACTION_LOGGER = true;
    window.DP_DEV_MODE = true;

    // This builder calls compile on old-style reducers
    // created via the Reducer class
    const legacyReducerBuilder = function(reducer) {
      if (reducer.isAmplifluxReducer) {
        const rInst = new reducer();
        return rInst.compile();
      }

      return reducer;
    };

    // Bootstrap DAL
    loadRepositoriesConfig(repositoriesConfig);

    const reducer = combineReducerHierarchy(AppReducers, legacyReducerBuilder);
    const middleware = applyMiddleware(
      ampMiddleware.timerMiddleware('startTime'),
      ampMiddleware.intervalMiddleware,
      ampMiddleware.timeoutMiddleware,
      ampMiddleware.actionThunkMiddleware,
      ampMiddleware.redispatchDsaPayload,
      ampMiddleware.guidMiddleware,
      ampMiddleware.promiseMiddleware
      ,ampMiddleware.loggerMiddleware
    );
    const makeStore = compose(middleware)(createStore);
    const store = makeStore(reducer);

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
}
