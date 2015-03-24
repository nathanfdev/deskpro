require("babel/polyfill");

import reflux from "reflux";
import Container from "DeskPRO/Component/DependencyInjection/Container";
import Http from "DeskPRO/Component/Http/Http";
import UrlCorrector from "DeskPRO/Bundle/AppBundle/Http/UrlCorrector";
import $ from "jquery";

export default class PortalApp {
  constructor() {
    this.container = new Container();
    this.container.register('$', { constant: $, alias: ['jquery', 'jQuery'] });
    this.container.registerFactory('http', ['jQuery', (jQuery) => {
      let http = new Http(jQuery.ajax);
      http.enableJsonPayloads();

      http.addInterceptor(new UrlCorrector(window.DP_BASE_URL));

      return http;
    }]);
  }

  run() {
    console.log("RUN");
  }
}