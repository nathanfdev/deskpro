import "babel/polyfill";
import $ from "jquery";
import React from 'react';

import { createStore, applyMiddleware, combineReducers } from 'redux';
import { Provider } from 'react-redux';

import * as ampMiddleware from "Ampliflux/middleware";

import BrowserHistory from 'react-router/lib/BrowserHistory';

import AppReducers from "./AgentApp_Reducers.js";

import DpAppContainer from "DeskPRO/Bundle/AgentBundle/Modules/Application/Components/DpAppContainer";

function legacyCombineReducers(reducers) {
  let processed_reducers = {};
  for(let k in reducers) {
    try {
      if(reducers[k].isAmplifluxReducer && reducers[k].isAmplifluxReducer()) {
        let reducer = new reducers[k]();
        processed_reducers[k] = reducer.compile();
      } else {
        processed_reducers[k] = reducers[k];
      }
    }
    catch(err) {
      processed_reducers[k] = reducers[k];
    }
  }

  return combineReducers(processed_reducers);
}


function combineAppReducers(appReducersMap) {
  let reducers = {};
  for (let k in appReducersMap) {
    reducers[k] = legacyCombineReducers(appReducersMap[k]);
  }

  return combineReducers(reducers);
}

export default class AgentApp {
  run() {
    $(document).on('ready', () => this.start());
  }

  start() {

    window.DP_ENABLE_ACTION_LOGGER = true;
    const reducer    = combineAppReducers(AppReducers);
    const middleware = applyMiddleware(
      ampMiddleware.intervalMiddleware,
      ampMiddleware.timeoutMiddleware,
      ampMiddleware.actionThunkMiddleware,
      ampMiddleware.guidMiddleware,
      ampMiddleware.loggerMiddleware,
      ampMiddleware.promiseMiddleware
    );
    const makeStore  = middleware(createStore);
    const store      = makeStore(reducer);

    var intlData = {
      "locales": "en-US",
      "messages": {
        "foobar": "Tickets"
      }
    };

    const hist = new BrowserHistory();

    React.render(
      <Provider store={store}>
        {() => <DpAppContainer {...intlData} history={hist} />}
      </Provider>,
      document.getElementById('deskpro_app_window')
    );
  }
}
