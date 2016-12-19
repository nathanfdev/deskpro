import React from 'react';
import ReactDOM from 'react-dom';

/**
 * This component is not the same as DeskPRO/Component/Positioned/Detached
 * it only renders subtree into the body and nothing else
 */
export class Detached extends React.Component {

  componentDidUpdate() {
    if (!this.props.children) {
      return this.componentWillUnmount();
    }

    if (!this.node) {
      this.node = document.createElement('div');
      document.body.appendChild(this.node);
    }

    ReactDOM.unstable_renderSubtreeIntoContainer(this,
      <div className="detached">{this.props.children}</div>, this.node);
  }

  componentWillUnmount() {
    if (!this.node) return;

    ReactDOM.unmountComponentAtNode(this.node);
    this.node.parentNode.removeChild(this.node);
    this.node = null;
  }

  render() {
    return <div className="detached" />;
  }
}
