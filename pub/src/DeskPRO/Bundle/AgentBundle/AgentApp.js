import "babel/polyfill";
import $ from "jquery";
import React from 'react';

import { createStore, applyMiddleware, compose } from 'redux';
import { Provider } from 'react-redux';

import { combineReducerHierarchy } from "Ampliflux";
import * as ampMiddleware from "Ampliflux/middleware";

import BrowserHistory from 'react-router/lib/BrowserHistory';

import AppReducers from "./AgentApp_Reducers.js";

import DpAppContainer from "DeskPRO/Bundle/AgentBundle/Modules/Application/Components/DpAppContainer";

import { DevTools, DebugPanel, LogMonitor } from 'redux-devtools/lib/react';
import { devTools } from 'redux-devtools';

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
        const realR = rInst.compile();
        return realR;
      } else {
        return r;
      }
    };

    const reducer    = combineReducerHierarchy(AppReducers, legacyReducerBuilder);
    const middleware = applyMiddleware(
      ampMiddleware.intervalMiddleware,
      ampMiddleware.timeoutMiddleware,
      ampMiddleware.actionThunkMiddleware,
      ampMiddleware.guidMiddleware,
      ampMiddleware.loggerMiddleware,
      ampMiddleware.promiseMiddleware
    );
    const makeStore  = middleware(compose(devTools(), createStore));
    const store      = makeStore(reducer);

    var intlData = {
      "locales": "en-US",
      "messages": {
        "foobar": "Tickets"
      }
    };

    const hist = new BrowserHistory();

    let els = [
      <Provider store={store}>
        {() => <DpAppContainer {...intlData} history={hist} />}
      </Provider>
    ];

    if (window.DP_DEV_MODE) {
      $('body').addClass('with-debug-panel');
      els.push(
        <DebugPanel top right bottom key="debugPanel">
          <DevTools store={store} monitor={LogMonitor}/>
        </DebugPanel>
      );
    }

    React.render(
      <div>{els}</div>,
      document.getElementById('deskpro_app_window')
    );
  }
}
