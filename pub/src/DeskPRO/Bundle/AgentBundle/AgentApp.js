import "babel/polyfill";
import $ from "jquery";
import React from 'react';
import { createRedux, createDispatcher, composeStores } from 'redux';
import thunkMiddleware from 'redux/lib/middleware/thunk';
import promiseMiddleware from 'redux-promise';
import { Provider } from 'redux/react';
import { composeReducers } from "Ampliflux/reducers";
import BrowserHistory from 'react-router/lib/BrowserHistory';

import * as app_stores from "DeskPRO/Bundle/AgentBundle/Modules/Application/Reducers/index";
import * as ticket_stores from "DeskPRO/Bundle/AgentBundle/Modules/Tickets/Reducers/index";
import * as task_stores from "DeskPRO/Bundle/AgentBundle/Modules/Tasks/Reducers/index";
import * as chat_stores from "DeskPRO/Bundle/AgentBundle/Modules/Chat/Reducers/index";
import * as crm_stores from "DeskPRO/Bundle/AgentBundle/Modules/CRM/Reducers/index";
import * as feedback_stores from "DeskPRO/Bundle/AgentBundle/Modules/Feedback/Reducers/index";

import { DpAppContainer } from "DeskPRO/Bundle/AgentBundle/Modules/Application/Components/DpAppContainer";

export default class AgentApp {
  run() {
    $(document).on('ready', () => this.start());
  }

  start() {
    const store = composeReducers(Object.assign({}, app_stores, ticket_stores, task_stores, chat_stores, crm_stores));

    const dispatcher = createDispatcher(
      store,
      getState => [thunkMiddleware(getState), promiseMiddleware]
    );

    const redux = createRedux(dispatcher);

    var intlData = {
      "locales": "en-US",
      "messages": {
        "foobar": "Tickets"
      }
    };
    
    const hist = new BrowserHistory();

    React.render(
      <Provider redux={redux}>
        {() => <DpAppContainer {...intlData} history={hist} />}
      </Provider>,
      document.getElementById('deskpro_app_window')
    );
  }
}
