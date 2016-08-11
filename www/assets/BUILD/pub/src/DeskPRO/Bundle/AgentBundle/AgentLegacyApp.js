import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import AgentTopBar from './Modules/TopBar/AgentTopBar';

export class AgentLegacyApp {
  run() {
    window.$(document).on('ready', () => this.start());
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
