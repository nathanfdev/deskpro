require("babel/polyfill");

import Http from "DeskPRO/Component/Http/Http";
import UrlCorrector from "DeskPRO/Bundle/AppBundle/Http/UrlCorrector";
import PortalPage from "DeskPRO/Bundle/PortalBundle/PageWidget/PortalPage";
import $ from "jquery";

export default class PortalApp {
  constructor() {
    window.$ = $;
    window.PortalApp = this;
  }

  run() {
    let page = new PortalPage();
    page.renderWhenReady();
  }
}
