import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { Abstract } from './Abstract';

export class Simple extends Abstract {

  /**
   * @inheritDoc
   */
  static propTypes = {
    isOpen: PropTypes.bool.isRequired,
    children: PropTypes.any
  };

  /**
   * Run when the component has been mounted
   * Because componentDidUpdate fires only after the first render here we should call it directly
   * @returns {void}
   */
  componentDidMount() {
    this.node = ReactDOM.findDOMNode(this);
    this.updatePosition();
  }

  /**
   * Override parent method with the way without mutate dom
   * @param  {Object} newProps The new props
   * @return {void}
   */
  componentWillReceiveProps(newProps) {
    this.props = newProps;
  }

  /**
   * We should update our position in case rerender
   * @return {void}
   */
  componentDidUpdate() {
    this.updatePosition();
  }

  /**
   * Render directly insteadof renderContent, the last one causes DOM mutations
   * @return {XML} The rendered element
   */
  render() {
    const { isOpen, children } = this.props;

    // Render the component with react, or don't if the prop changes
    return isOpen ? <div className="positioned-element">{children}</div> : <div/>;
  }
}
