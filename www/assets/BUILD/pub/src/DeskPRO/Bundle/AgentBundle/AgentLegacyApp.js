import 'babel-polyfill';
import jQuery from 'jquery';
import React from 'react';
import ReactDOM from 'react-dom';
import AgentTopBar from './Modules/TopBar/AgentTopBar';

export class AgentLegacyApp {
  run() {
    jQuery(document).on('ready', () => this.start());
  }

  start() {
    if (typeof window.DeskPRO_Window === 'undefined') {
      setTimeout(this.start.bind(this), 100);
    } else {
      ReactDOM.render(
        <AgentTopBar />,
        document.getElementById('dp_header')
      );
    }
  }
}
