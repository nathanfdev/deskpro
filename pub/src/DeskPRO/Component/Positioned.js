import React from 'react';
import $ from 'jquery';
import position from 'jquery-ui/position';

export default class Positioned extends React.Component {

  /**
   * The valid PropTypes for this component
   * @type {Object}
   */
  static propTypes = {
    isOpen: React.PropTypes.bool.isRequired,
    positionCalc: React.PropTypes.func,
    position: React.PropTypes.object,
    positionAt: React.PropTypes.string,
    positionMy: React.PropTypes.string,
    positionTarget: React.PropTypes.any,
    collision: React.PropTypes.object,
    onOpen: React.PropTypes.func,
    onClose: React.PropTypes.func
  }

  /**
   * Run when the component has been mounted
   * @returns {void}
   */
  componentDidMount() {
    this.node = React.findDOMNode(this);
    $(this.node).detach();
    $('body').prepend(this.node);

    // Manipulate the DOM here
    this.renderContent();
  }

  /**
   * Re-render the dialog content when we get a new set of props
   * @param  {Object} newProps The new props
   * @return {void}
   */
  componentWillReceiveProps(newProps) {
    // Re-render the dialog box with the new properties when there's a change
    this.renderContent(newProps);
  }

  /**
   * Code to run before the component is destroyed.
   * Removes the corresponding element from the DOM
   * @return {void}
   */
  componentWillUnmount() {
    // Clean up the DOM when the component is umounted
    React.unmountComponentAtNode(this.node);
    $(this.node).remove();
  }

  /**
   * Update the position of the component
   * @param  {Object} element DOM element to move
   * @return {void}
   */
  updatePosition() {
    const placement = this.props.position || {
      my: 'top left',
      at: 'bottom right',
      of: null,
      collision: 'none'
    };

    placement.my = this.props.positionMy || placement.my;
    placement.at = this.props.positionAt || placement.at;

    if (this.props.positionTarget) {
      placement.of = this.props.positionTarget;
      if (React.findDOMNode(this.props.positionTarget) !== null) {
        placement.of = React.findDOMNode(this.props.positionTarget);
      }

      placement.collision = this.props.collision || placement.collision;

      // Error out if we don't have a position target
      if (placement.of === null) {
        console.error('No position target specified');
      }

      $(this.node).css('position', 'absolute').position(placement);
    }
  }

  /**
   * Render the contents of the dialog
   * @param  {Object} props The props to use
   * @return {void}
   */
  renderContent() {
    // Render the component with react, or don't if the prop changes
    if (this.props.isOpen) {
      // Put the element inside a div that we can position
      React.render(<div className="positioned-element">{this.props.children}</div>, this.node);
      this.updatePosition();
    } else {
      React.render(<div />, this.node);
    }
  }

  /**
   * Render a fake div in place of the element
   * @return {React.Element} The rendered element
   */
  render() {
    return (<div/>);
  }
}
