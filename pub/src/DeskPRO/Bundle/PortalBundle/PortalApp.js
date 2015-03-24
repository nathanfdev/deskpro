require("babel/polyfill");

import Container from "DeskPRO/Component/DependencyInjection/Container";
import Http from "DeskPRO/Component/Http/Http";
import UrlCorrector from "DeskPRO/Bundle/AppBundle/Http/UrlCorrector";
import PortalPage from "DeskPRO/Bundle/PortalBundle/PageWidget/PortalPage";
import ReactRegistry from "DeskPRO/Bundle/PortalBundle/ReactRegistry";
import $ from "jquery";

export default class PortalApp {
  constructor() {
    this.container = new Container();
    this.container.registerFactory('http', function() {
      let http = new Http(j$.ajax);
      http.enableJsonPayloads();

      http.addInterceptor(new UrlCorrector(window.DP_BASE_URL));

      return http;
    });
    this.container.registerFactory('ReactReg', ['container', (c) => { return new ReactRegistry(c) } ]);
  }

  run() {
    let page = new PortalPage(this.container);
    page.render();
  }
}