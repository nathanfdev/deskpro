import React from 'react';
import ReactDOM from 'react-dom';
import jQuery from 'jquery';
import position from 'jquery-ui/position';

export default class Abstract extends React.Component {

  /**
   * The valid PropTypes for this component
   * @type {Object}
   */
  static propTypes = {
    isOpen: React.PropTypes.bool,
    positionCalc: React.PropTypes.func,
    position: React.PropTypes.object,
    positionAt: React.PropTypes.string,
    positionMy: React.PropTypes.string,
    positionTarget: React.PropTypes.any,
    collision: React.PropTypes.object,
    onOpen: React.PropTypes.func,
    onClose: React.PropTypes.func,
    children: React.PropTypes.any
  };

  /**
   * Constructor
   * @param  {Object} props The props for the objject
   * @return {void}
   */
  constructor(props) {
    super(props);
    this.state = {
      isOpen: false
    };
  }

  /**
   * Re-render the dialog content when we get a new set of props
   * @param  {Object} newProps The new props
   * @return {void}
   */
  componentWillReceiveProps(newProps) {
    // Re-render the dialog box with the new properties when there's a change
    this.props = newProps;
    this.renderContent();
  }

  /**
   * Code to run before the component is destroyed.
   * Removes the corresponding element from the DOM
   * @return {void}
   */
  componentWillUnmount() {
    // Clean up the DOM when the component is umounted
    ReactDOM.unmountComponentAtNode(this.node);
    jQuery(this.node).remove();
  }

  /**
   * Update the position of the component
   * @return {void}
   */
  updatePosition() {
    if (this.props.positionCalc) {
      const positionResult = this.props.positionCalc();
      jQuery(this.node).css('position', 'absolute')
        .css('top', positionResult.top)
        .css('left', positionResult.left);
    } else {
      const placement = this.props.position ||
        {
          my: 'left top',
          at: 'right bottom',
          of: null,
          collision: 'none'
        };

      placement.my = this.props.positionMy || placement.my;
      placement.at = this.props.positionAt || placement.at;

      if (this.props.positionTarget) {
        placement.of = this.props.positionTarget;
        if (!(this.props.positionTarget instanceof jQuery) && ReactDOM.findDOMNode(this.props.positionTarget) !== null) {
          placement.of = ReactDOM.findDOMNode(this.props.positionTarget);
        }

        placement.collision = this.props.collision || placement.collision;

        // Error out if we don't have a position target
        if (placement.of === null) {
          console.error('No position target specified');
        }

        jQuery(this.node).css('position', 'absolute').position(placement);
      }
    }
  }

  /**
   * Calculate whether onOpen/onClose should fire
   * @return {bool} Whether the function should run
   */
  shouldFire() {
    const isOpen = this.props.isOpen || false;
    const fire = (isOpen === this.state.isOpen);

    this.setState({
      isOpen: isOpen
    });

    return fire;
  }

  /**
   * Render the contents of the dialog
   * @return {void}
   */
  renderContent() {
    const isOpen = this.props.isOpen || false;

    const renderSubtreeIntoContainer = ReactDOM.unstable_renderSubtreeIntoContainer;

    // Render the component with react, or don't if the prop changes
    if (isOpen) {
      // Put the element inside a div that we can position
      renderSubtreeIntoContainer(this, <div className="positioned-element">{this.props.children}</div>, this.node);
      this.updatePosition();
      if (this.shouldFire() && this.props.onOpen) {
        this.props.onOpen();
      }
    } else {
      renderSubtreeIntoContainer(this, <div />, this.node);
      if (this.shouldFire() && this.props.onClose) {
        this.props.onClose();
      }
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
