import { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import Abstract from './Abstract';
import jQuery from 'jquery';

export default class Detached extends Abstract {

  static propTypes = {
    context: PropTypes.any
  };

  /**
   * Run when the component has been mounted
   * @returns {void}
   */
  componentDidMount() {
    this.node = ReactDOM.findDOMNode(this);
    jQuery(this.node).detach();
    jQuery(this.props.context || 'body').prepend(this.node);

    // Manipulate the DOM here
    this.renderContent();
  }
}
