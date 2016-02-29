import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import invariant from 'invariant';

/**
 * This component is not the same as DeskPRO/Component/Positioned/Detached
 * it only renders subtree into the body and nothing else
 */
export class Detached extends React.Component {

  componentWillUnmount() {
    if (!this.node) return;

    ReactDOM.unmountComponentAtNode(this.node);
    this.node.parentNode.removeChild(this.node);
    this.node = null;
  }

  componentDidUpdate() {
    if (!this.props.children) {
      return this.componentWillUnmount();
    }

    if (!this.node) {
      this.node = document.createElement('div');
      document.body.appendChild(this.node);
    }

    ReactDOM.unstable_renderSubtreeIntoContainer(this, <div className="detached">{this.props.children}</div>, this.node);
  }

  render() {
    return <div className="detached" />;
  }
}
