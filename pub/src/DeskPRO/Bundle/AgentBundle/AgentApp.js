require("DeskPRO/Bundle/AgentBundle/Resources/style/agent-style.scss");

import "babel/polyfill";
import Container from "DeskPRO/Component/DependencyInjection/Container";
import App from "DeskPRO/Component/DependencyInjection/AppContainer";
import Http from "DeskPRO/Component/Http/Http";
import UrlCorrector from "DeskPRO/Bundle/AppBundle/Http/UrlCorrector";
import $ from "jquery";

export default class AgentApp {
  constructor() {
    window.App = App;
    window.AgentApp = this;

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
    console.log("OK");
  }
}