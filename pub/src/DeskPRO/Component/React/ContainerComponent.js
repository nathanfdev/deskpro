import Component from "./Component";

export default class ConatinerComponent extends Component {
  constructor(props) {

    let classInit = this.init;
    classInit.$inject = this.init$inject();
    this.init = () => {
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
