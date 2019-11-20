import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { AppContainer } from 'react-hot-loader';
import $ from 'jquery';
import { addLocaleData, IntlProvider } from 'react-intl';
import PortalPage from './PageWidget/PortalPage';
import { portalPhrases } from './PortalPhrases';
import App from './Modules/Application/Components/PortalAppContainer';

const possibleLocale = (window.DESKPRO_LOCALE || 'en').replace(/-/, '_').split(/_/)[0] || 'en';
import(
  /* webpackPreload: true */
  `react-intl/locale-data/${possibleLocale}`
)
  .then(({ "default": data }) => addLocaleData(data))
  .catch(err => {
    console.log(`Failed to load ${possibleLocale}, fallback on en`);
    import(`react-intl/locale-data/en`).then(({ "default": data }) => addLocaleData(data));
  });

// Async load FA
import('@fortawesome/fontawesome-pro/js/all.min');

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
    page.renderWhenReady();
    this.portalPage = page;
    window.DESKPRO_PORTAL_PAGE = page;
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
