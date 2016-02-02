import { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import { Abstract } from './Abstract';

export class Detached extends Abstract {

  static propTypes = {
    context: PropTypes.any
  };

  /**
   * Run when the component has been mounted
   * @returns {void}
   */
  componentDidMount() {
    this.node = ReactDOM.findDOMNode(this);

    $(this.node).detach();
    $(this.props.context || 'body').prepend(this.node);

    // Manipulate the DOM here
    this.renderContent();
  }
}
