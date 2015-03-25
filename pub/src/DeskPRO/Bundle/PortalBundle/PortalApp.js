require("babel/polyfill");

import Container from "DeskPRO/Component/DependencyInjection/Container";
import App from "DeskPRO/Component/DependencyInjection/AppContainer";
import Http from "DeskPRO/Component/Http/Http";
import UrlCorrector from "DeskPRO/Bundle/AppBundle/Http/UrlCorrector";
import PortalPage from "DeskPRO/Bundle/PortalBundle/PageWidget/PortalPage";
import $ from "jquery";

export default class PortalApp {
  constructor() {
    window.$ = $;
    window.App = App;
    window.PortalApp = this;

    this.container = new Container();
    App.setContainer(this.container);

    this.container.registerFactory('http', function() {
      let http = new Http(j$.ajax);
      http.enableJsonPayloads();

      http.addInterceptor(new UrlCorrector(window.DP_BASE_URL));

      return http;
    });
  }

  run() {
    let page = new PortalPage(this.container);
    page.render();
  }
}