import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import Abstract from './Abstract';

export default class Simple extends Abstract {

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
    this.node = ReactDOM.findDOMNode(this); // I'm not sure if we need this?
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
   * @return {React.Element} The rendered element
   */
  render() {
    let render;
    // Render the component with react, or don't if the prop changes
    if (this.props.isOpen) {
      render = <div className="positioned-element">{this.props.children}</div>;
    } else {
      render = <div />;
    }
    return render;
  }
}
