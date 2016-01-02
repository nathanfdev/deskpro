require('babel/polyfill');

import Http from 'DeskPRO/Component/Http/Http';
import UrlCorrector from 'DeskPRO/Bundle/AppBundle/Http/UrlCorrector';
import PortalPage from 'DeskPRO/Bundle/PortalBundle/PageWidget/PortalPage';
import PortalPhrases from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import $ from 'jquery';

class PortalApp {
  constructor() {
    window.$ = $;
    window.PortalApp = this;

    this.phrases = PortalPhrases;
    if (window.DESKPRO_PHRASES) {
      this.phrases.setPhrases(window.DESKPRO_PHRASES);
    }
  }

  getPortalPage() {
    return this._portalPage;
  }

  run() {
    const page = new PortalPage();
    page.renderWhenReady();
    this._portalPage = page;
  }
}

const app = new PortalApp();

export default app;
