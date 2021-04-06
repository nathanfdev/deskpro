import React from 'react';
import ReactDOM from 'react-dom';
import 'babel-polyfill';
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/pro-solid-svg-icons';
import { faAngleDown, faAngleRight, faAngleLeft, faInfoCircle, faExclamationCircle, faTimes } from '@fortawesome/pro-light-svg-icons';
import { faSearch, faAngleDown as farAngleDown, faSpinner } from '@fortawesome/pro-regular-svg-icons';
import { AppContainer } from 'react-hot-loader';
import $ from 'jquery';
import { IntlProvider } from 'react-intl';
import '@fortawesome/fontawesome-pro/js/all.min';
import HelpCenterPage from './PageWidget/HelpCenterPage';
import { portalPhrases } from './PortalPhrases';
import App from './Modules/Application/Components/AppContainer';

import { portalHttp } from './Http/PortalHttp';

class HelpcenterApp {
  constructor() {
    window.jQuery = window.$ = $;
    window.PortalApp = this;

    this.phrases = portalPhrases;
    if (window.DESKPRO_PHRASES) {
      this.phrases.setPhrases(window.DESKPRO_PHRASES);
    }
    library.add(fas, faAngleDown, faAngleRight, faAngleLeft, faInfoCircle, faExclamationCircle, faSearch, farAngleDown, faSpinner, faTimes);

    this.locale = window.DESKPRO_LOCALE.replace(/_/, '-').split(/_/)[0] || 'en';
  }

  getPortalPage() {
    return this.portalPage;
  }

  run() {
    const page = new HelpCenterPage();
    page.renderWhenReady();
    this.portalPage = page;
    window.DESKPRO_PORTAL_PAGE = page;
  }

  getPhraseMessages() {
    const messages = portalPhrases.getPhrases();

    if (Object.keys(messages).length !== 0) {
      return messages;
    }

    portalHttp.sendGet(`DP_URL/portal/api/lang/widget-phrases.json?language=${this.locale}`).then((response) => {
      if (response.data.phrases) {
        portalPhrases.setPhrases(response.data.phrases);
        return portalPhrases.getPhrases();
      }

      return messages;
    });

    return messages;
  }

  render(props, node) {
    const messages = this.getPhraseMessages();
    ReactDOM.render(
      <AppContainer>
        <IntlProvider
          locale={this.locale}
          messages={messages}
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
