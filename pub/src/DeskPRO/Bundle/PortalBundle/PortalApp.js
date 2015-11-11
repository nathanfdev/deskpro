require("babel/polyfill");

import Http from "DeskPRO/Component/Http/Http";
import UrlCorrector from "DeskPRO/Bundle/AppBundle/Http/UrlCorrector";
import PortalPage from "DeskPRO/Bundle/PortalBundle/PageWidget/PortalPage";
import $ from "jquery";

class PortalApp {
  constructor() {
    window.$ = $;
    window.PortalApp = this;
  }

  getPortalPage() {
    return this._portalPage;
  }

  run() {
    let page = new PortalPage();
    page.renderWhenReady();
    this._portalPage = page;
  }
}

const app = new PortalApp();

export default app;
