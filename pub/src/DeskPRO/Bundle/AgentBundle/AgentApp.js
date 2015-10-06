import 'babel/polyfill';
import $ from 'jquery';
import React from 'react';
import ReactDOM from 'react-dom';
import { createStore, applyMiddleware, compose } from 'redux';
import { Provider } from 'react-redux';
import { combineReducerHierarchy } from 'Ampliflux';
import * as ampMiddleware from 'Ampliflux/middleware';
import BrowserHistory from 'react-router/lib/BrowserHistory';
import AppReducers from './AgentApp_Reducers.js';
import { DpAppContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/DpAppContainer';
import { batchedUpdatesMiddleware } from 'redux-batched-updates';
import { IntlProvider, defineMessages } from 'react-intl';

import Immutable from 'immutable';
window.Immutable = Immutable;

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

export default class AgentApp {
  run() {
    $(document).on('ready', () => this.start());
  }

  start() {
    window.DP_ENABLE_ACTION_LOGGER = true;
    window.DP_DEV_MODE = true;

    // This builder calls compile on old-style reducers
    // created via the Reducer class
    const legacyReducerBuilder = function(r) {
      if (r.isAmplifluxReducer) {
        const rInst = new r();
        return rInst.compile();
      } else {
        return r;
      }
    };

    const reducer    = combineReducerHierarchy(AppReducers, legacyReducerBuilder);
    const middleware = applyMiddleware(
      ampMiddleware.timerMiddleware('startTime'),
      ampMiddleware.intervalMiddleware,
      ampMiddleware.timeoutMiddleware,
      ampMiddleware.actionThunkMiddleware,
      ampMiddleware.redispatchDsaPayload,
      ampMiddleware.guidMiddleware,
      ampMiddleware.promiseMiddleware,
      ampMiddleware.loggerMiddleware,
      batchedUpdatesMiddleware
    );
    const makeStore  = compose(
        middleware
    )(createStore);
    const store      = makeStore(reducer);

    const intlData = {
      'locales': 'en-US',
      'messages': {
        'foobar': 'Tickets'
      }
    };

    const hist = new BrowserHistory();

    ReactDOM.render(
      <div>
        <Provider store={store}>
          <IntlProvider locale={window.DP_LOCALE} messages={window.DP_LANG}>
            <DpAppContainer {...intlData} history={hist} />
          </IntlProvider>
        </Provider>
      </div>,
      document.getElementById('deskpro_app_window')
    );
  }
}
