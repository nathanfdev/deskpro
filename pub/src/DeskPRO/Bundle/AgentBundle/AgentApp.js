import "babel/polyfill";
import $ from "jquery";
import React from 'react';

import { createStore, applyMiddleware } from 'redux';
import promiseMiddleware from 'redux-promise';
import thunkMiddleware from "redux-thunk";
import { Provider } from 'react-redux';

import { combineReducers } from "Ampliflux/reducers";

import BrowserHistory from 'react-router/lib/BrowserHistory';

import * as app_stores from "DeskPRO/Bundle/AgentBundle/Modules/Application/Reducers/index";
import * as ticket_stores from "DeskPRO/Bundle/AgentBundle/Modules/Tickets/Reducers/index";
import * as task_stores from "DeskPRO/Bundle/AgentBundle/Modules/Tasks/Reducers/index";

import DpAppContainer from "DeskPRO/Bundle/AgentBundle/Modules/Application/Components/DpAppContainer";

export default class AgentApp {
  run() {
    $(document).on('ready', () => this.start());
  }

  start() {

    const reducer    = combineReducers(Object.assign({}, app_stores, ticket_stores, task_stores));
    const middleware = applyMiddleware(thunkMiddleware, promiseMiddleware);
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
