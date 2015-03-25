import Component from "./Component";
import App from "DeskPRO/Component/DependencyInjection/AppContainer";

export default class ConatinerComponent extends Component {
  constructor(props) {
    this.container = App.getContainer();

    let classInit = this.init;
    classInit.$inject = this.init$inject();
    this.init = () => {
      this.container.invoke(classInit, this);
    };

    super(props);
  }

  /**
   * Return an array of objects to inject into init.
   *
   * @returns {Array}
   */
  init$inject() { return []; }
}