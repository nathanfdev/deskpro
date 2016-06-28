import React from 'react';
import ReactDOM from 'react-dom';
import { Abstract } from './Abstract';

export class Detached extends Abstract {

  static propTypes = {
    context: React.PropTypes.any
  };

  componentWillUnmount() {
    if (this.cont) {
      this.props.children && ReactDOM.unmountComponentAtNode(this.cont);
      this.cont.parentNode.removeChild(this.cont);
      this.cont = null;
    }
  }

  componentDidUpdate() {
    const { isOpen = false } = this.state;
    const { onOpen, onClose } = this.props;

    if (isOpen) {
      if (!this.cont) {
        this.cont = document.createElement('div');
        this.cont.className = 'positioned-element';
        document.body.appendChild(this.cont);
      }

      ReactDOM.unstable_renderSubtreeIntoContainer(this, this.props.children, this.cont);
      onOpen && onOpen();
      this.updatePosition();
    } else {
      if (this.cont) {
        this.props.children && ReactDOM.unmountComponentAtNode(this.cont);
        this.cont.parentNode.removeChild(this.cont);
        this.cont = null;
      }

      onClose && onClose();
    }
  }
}
