import 'babel/polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import style from './Resources/style/widget-style.scss';
import { App } from './Modules/Application/Components/App';
import jQuery from 'jquery';

export default class WidgetApp {
  run() {
    jQuery(document).on('ready', () => this.start());
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
      console.error('No parent document');
      return;
    }

    // - NOTICE: We are rendering the react root element onto
    // the parent page.
    // - This way, we can render <Frame>'s and they are added
    // to the parent DOM and can be positioned properly.
    // - From a react app point of view, it doesn't know that
    // the DOM is on a parent frame and the JS/state is on this page. Cool!

    const $container = jQuery('<div>', {
      id: 'dp_widget_container',
      css: {
        display: 'block',
        width: '1px',
        height: '1px'
      }
    });

    $container.appendTo(pageDoc.body);
    ReactDOM.render(<App />, $container.get(0));
  }
}
