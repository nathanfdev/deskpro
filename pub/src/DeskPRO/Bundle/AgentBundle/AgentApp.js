import "babel/polyfill";
import $ from "jquery";
import React from 'react';
import { createRedux, createDispatcher, composeStores } from 'redux';
import thunkMiddleware from 'redux/lib/middleware/thunk';
import promiseMiddleware from 'redux-promise';
import { Provider } from 'redux/react';
import { composeReducers } from "Ampliflux/reducers";

import * as app_stores from "DeskPRO/Bundle/AgentBundle/Modules/Application/Reducers/index";
import * as ticket_stores from "DeskPRO/Bundle/AgentBundle/Modules/Tickets/Reducers/index";
import * as task_stores from "DeskPRO/Bundle/AgentBundle/Modules/Tasks/Reducers/index";

import DpAppContainer from "DeskPRO/Bundle/AgentBundle/Modules/Application/Components/DpAppContainer";

export default class AgentApp {
  run() {
    $(document).on('ready', () => this.start());
  }

  start() {
    const store = composeReducers(Object.assign({}, app_stores, ticket_stores, task_stores));

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

    React.render(
      <Provider redux={redux}>
        {() => <DpAppContainer {...intlData} />}
      </Provider>,
      document.getElementById('deskpro_app_window')
    );
  }
}
