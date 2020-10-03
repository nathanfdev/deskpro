import 'babel-polyfill';
import React from 'react';
import ReactDOM from 'react-dom';
import { AppContainer } from 'react-hot-loader';
import $ from 'jquery';
import { IntlProvider } from 'react-intl';
import HelpCenterPage from './PageWidget/HelpCenterPage';
import { portalPhrases } from './PortalPhrases';
import App from './Modules/Application/Components/AppContainer';
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/pro-solid-svg-icons';
import { faAngleDown, faAngleRight, faAngleLeft, faInfoCircle, faExclamationCircle } from '@fortawesome/pro-light-svg-icons';
import { faSearch, faAngleDown as farAngleDown, faSpinner } from '@fortawesome/pro-regular-svg-icons';

// Async load FA
import('@fortawesome/fontawesome-pro/js/all.min');

class HelpcenterApp {
  constructor() {
    window.jQuery = window.$ = $;
    window.PortalApp = this;

    this.phrases = portalPhrases;
    if (window.DESKPRO_PHRASES) {
      this.phrases.setPhrases(window.DESKPRO_PHRASES);
    }
    library.add(fas, faAngleDown, faAngleRight, faAngleLeft, faInfoCircle, faExclamationCircle, faSearch, farAngleDown, faSpinner);
  }

  getPortalPage() {
    return this.portalPage;
  }

  run() {
    const page = new HelpCenterPage();
    this.locale = window.DESKPRO_LOCALE.replace(/_/, '-').split(/_/)[0] || 'en';
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

export const helpcenterApp = new HelpcenterApp();
