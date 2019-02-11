import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { AppContainer } from 'react-hot-loader';
import $ from 'jquery';
import { addLocaleData, IntlProvider } from 'react-intl';
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

    this.locale = window.DESKPRO_LOCALE.replace(/_/, '-');

    const possibleLocale = window.DESKPRO_LOCALE.replace(/-/, '_').split(/_/)[0] || 'en';
    try {
      addLocaleData(require(`react-intl/locale-data/${possibleLocale}`)); // eslint-disable-line import/no-dynamic-require, global-require
    } catch (e) {
      addLocaleData(require('react-intl/locale-data/en')); // eslint-disable-line import/no-dynamic-require, global-require
    }

    page.renderWhenReady();
    this.portalPage = page;
  }

  render(props, node) {
    ReactDOM.render(
      <AppContainer>
        <IntlProvider
          locale={this.locale}
          messages={portalPhrases.getPhrases()}
        >
          <App {...props} />
        </IntlProvider>
      </AppContainer>, node);
  }
}

if (module.hot) {
  module.hot.accept();
}

export const portalApp = new PortalApp();
