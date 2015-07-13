require("DeskPRO/Bundle/AgentBundle/Resources/style/agent-style.scss");

import "babel/polyfill";
import $ from "jquery";
import React from 'react';
import { createRedux, createDispatcher, composeStores } from 'redux';
import thunkMiddleware from 'redux/lib/middleware/thunk';
import promiseMiddleware from 'redux-promise';
import { Provider } from 'redux/react';

import * as app_stores from "DeskPRO/Bundle/AgentBundle/Modules/Application/Stores/index";
import * as ticket_stores from "DeskPRO/Bundle/AgentBundle/Modules/Tickets/Stores/index";

import DpAppContainer from "DeskPRO/Bundle/AgentBundle/Modules/Application/Components/DpAppContainer";

export default class AgentApp {
  run() {
    $(document).on('ready', () => this.start());
  }

  start() {
    const store = composeStores(Object.assign({}, app_stores, ticket_stores));
    const dispatcher = createDispatcher(
      store,
      getState => [thunkMiddleware(getState), promiseMiddleware]
    );

    const redux = createRedux(dispatcher);

    React.render(
      <Provider redux={redux}>
        {() => <DpAppContainer />}
      </Provider>,
      document.getElementById('deskpro_app_window')
    );
  }
}
