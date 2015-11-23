import 'babel/polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { createStore, applyMiddleware, compose } from 'redux';
import { Provider } from 'react-redux';
import { combineReducerHierarchy } from 'Ampliflux';
import * as ampMiddleware from 'Ampliflux/middleware';
import AppReducers from './WidgetApp_Reducers.js';
import style from './Resources/style/widget-style.scss';

import { App } from './Modules/Application/Components/App';

export default class WidgetApp {
  run() {
    this.start();
  }

  start() {
    window.DP_ENABLE_ACTION_LOGGER = true;
    window.DP_DEV_MODE = true;

    // - NOTICE: The widget_loader creates an iframe on the host page
    // and includes this app into the frame.
    // - So here we're getting a reference to the parent document,
    // because below we will render the root react element into it instead.

    const pageDoc = (parent && parent.document) ? parent.document : null;
    if (!pageDoc) {
      console.error("No parent document");
      return;
    }

    const reducer = combineReducerHierarchy(AppReducers);
    const middleware = applyMiddleware(
      ampMiddleware.intervalMiddleware,
      ampMiddleware.timeoutMiddleware,
      ampMiddleware.actionThunkMiddleware,
      ampMiddleware.redispatchDsaPayload,
      ampMiddleware.promiseMiddleware,
      ampMiddleware.loggerMiddleware
    );
    const makeStore = compose(middleware)(createStore);
    const store = makeStore(reducer);

    // - NOTICE: We are rendering the react root element onto
    // the parent page.
    // - This way, we can render <Frame>'s and they are added
    // to the parent DOM and can be positioned properly.
    // - From a react app point of view, it doesn't know that
    // the DOM is on a parent frame and the JS/state is on this page. Cool!

    const dpWidgetContainer = pageDoc.createElement('div');
    dpWidgetContainer.id = "dp_widget_container";
    dpWidgetContainer.style.display = 'block';
    dpWidgetContainer.style.width = '1px';
    dpWidgetContainer.style.height = '1px';
    pageDoc.body.appendChild(dpWidgetContainer);

    ReactDOM.render(
      <Provider store={store}>
        <App />
      </Provider>,
      dpWidgetContainer
    );
  }
}
