import 'babel-polyfill';
import { PortalPage } from './PageWidget/PortalPage';
import { portalPhrases } from './PortalPhrases';
import $ from 'jquery';

class PortalApp {
  constructor() {
    window.$ = $;
    window.PortalApp = this;

    this.phrases = portalPhrases;
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
