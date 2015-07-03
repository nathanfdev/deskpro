require("DeskPRO/Bundle/AgentBundle/Resources/style/agent-style.scss");

import "babel/polyfill";
import $ from "jquery";
import React from 'react';
import { createRedux, createDispatcher, composeStores } from 'redux';
import thunkMiddleware from 'redux/lib/middleware/thunk';
import { Provider } from 'redux/react';

import DpWindow from "DeskPRO/Bundle/AgentBundle/Application/Component/DpWindow";

export default class AgentApp {
  run() {
    $(document).on('ready', () => this.start());
  }

  start() {
    const store = composeStores({});

    const dispatcher = createDispatcher(
      store,
      getState => [promiseMiddleware(), thunkMiddleware(getState)]
    );

    const redux = createRedux(dispatcher);

    React.render(
      <Provider redux={redux}>
        {() => <DpWindow />}
      </Provider>,
      document.getElementById('deskpro_app_window')
    );
  }
}
