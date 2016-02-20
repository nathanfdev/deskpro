import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import { Abstract } from './Abstract';

export class Detached extends Abstract {

  static propTypes = {
    context: PropTypes.any
  };

  componentDidUpdate() {
    const { isOpen = false } = this.state;
    const { onOpen, onClose } = this.props;

    if (isOpen) {
      if (!this.cont) {
        this.cont = document.createElement('div');
        this.cont.className = 'positioned-element';
        document.body.appendChild(this.cont);
      }

      this._renderLayer();
      onOpen && onOpen();
      this.updatePosition();

    } else if (this.cont) {
      ReactDOM.unmountComponentAtNode(this.cont);
      this.cont.parentNode.removeChild(this.cont);
      this.cont = null;

      onClose && onClose();
    }
  }

  _renderLayer() {
    this.cont && ReactDOM.render(this.props.children, this.cont);
  }
}
