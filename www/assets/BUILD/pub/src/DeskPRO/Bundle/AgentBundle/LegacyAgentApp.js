import 'babel-polyfill';
import jQuery from 'jquery';
import React from 'react';
import ReactDOM from 'react-dom';

export class LegacyAgentApp {
  run() {
    jQuery(document).on('ready', () => this.start());
  }

  start() {
    ReactDOM.render(
      <div></div>,
      document.getElementById('dp_header')
    );
  }
}
