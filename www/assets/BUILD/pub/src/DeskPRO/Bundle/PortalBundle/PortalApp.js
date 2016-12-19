import 'babel-polyfill';
import $ from 'jquery';
import PortalPage from './PageWidget/PortalPage';
import { portalPhrases } from './PortalPhrases';

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
}

export const portalApp = new PortalApp();
