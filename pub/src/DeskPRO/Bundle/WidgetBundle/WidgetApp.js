import 'babel-polyfill';
import React from 'react';
import { Provider } from 'react-redux';
import ReactDOM from 'react-dom';
import { AppContainer } from './Modules/Application/Components/AppContainer';
import $ from 'jquery';
import store from './Services/store';
import { bootstrapWidget } from './Modules/Application/Actions/bootstrapActions';
import './Resources/style/widget-style.scss';
import emitter from './Services/emitter';

export default class WidgetApp {

  run() {
    $(document).on('ready', this.start);
  }

  start() {
    window.DP_ENABLE_ACTION_LOGGER = true;
    window.DP_DEV_MODE = true;

    // - NOTICE: The widget_loader creates an iframe on the host page
    // and includes this app into the frame.
    // - So here we're getting a reference to the parent document,
    // because below we will render the root react element into it instead.

    const pageDoc = parent && parent.document;
    if (!pageDoc) {
      console.error('No parent document');
      return;
    }

    // - NOTICE: We are rendering the react root element onto
    // the parent page.
    // - This way, we can render <Frame>'s and they are added
    // to the parent DOM and can be positioned properly.
    // - From a react app point of view, it doesn't know that
    // the DOM is on a parent frame and the JS/state is on this page. Cool!

    const $container = $('<div>', {
      id: 'dp_widget_container',
      css: {
        display: 'block',
        width: '1px',
        height: '1px'
      }
    });

    store.dispatch(bootstrapWidget());
    window.emitter = emitter;

    const content = (
      <Provider store={store}>
        <AppContainer />
      </Provider>
    );

    $container.appendTo(pageDoc.body);
    ReactDOM.render(content, $container.get(0));
  }
}
