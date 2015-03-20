import reflux from "reflux";
import Container from "DeskPRO/Component/DependencyInjection/Container";
import $ from "jquery";

export default class PortalApp {
  constructor() {
    this.container = new Container();
    this.container.register('$', { constant: $, alias: ['jquery', 'jQuery'] });
  }
  run() {
    console.log("RUN");
  }
}