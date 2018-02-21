import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { AppContainer } from 'react-hot-loader';
import $ from 'jquery';
import PortalPage from './PageWidget/PortalPage';
import { portalPhrases } from './PortalPhrases';
import App from './Modules/Application/Components/AppContainer';

class PortalApp {
  constructor() {
    window.jQuery = window.$ = $;
    window.PortalApp = this;

    this.phrases = portalPhrases;
    if (window.DESKPRO_PHRASES) {
      this.phrases.setPhrases(window.DESKPRO_PHRASES);
    }
  }

  getPortalPage() {
    return this.portalPage;
  }

  run() {
    const page = new PortalPage();
    page.renderWhenReady();
    this.portalPage = page;
  }

  render(props, node) {
    ReactDOM.render(<AppContainer><App {...props} /></AppContainer>, node);
  }
}

if (module.hot) {
  module.hot.accept();
}

export const portalApp = new PortalApp();
