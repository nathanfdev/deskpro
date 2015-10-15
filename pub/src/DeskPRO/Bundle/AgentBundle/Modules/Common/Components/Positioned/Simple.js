import ReactDOM from 'react-dom';
import Abstract from './Abstract';

export default class Simple extends Abstract {

  /**
   * Run when the component has been mounted
   * @returns {void}
   */
  componentDidMount() {
    this.node = ReactDOM.findDOMNode(this);

    // Manipulate the DOM here
    this.renderContent(this.props);
  }
}
