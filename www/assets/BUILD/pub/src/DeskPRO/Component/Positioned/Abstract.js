import PropTypes from 'prop-types';
import React from 'react';
import ReactDOM from 'react-dom';
import invariant from 'invariant';
import $ from 'jquery';
import 'jquery-ui/jquery-ui';
import 'jquery-ui/position';

export class Abstract extends React.Component {

  /**
   * The valid PropTypes for this component
   * @type {Object}
   */
  static propTypes = {
    isOpen:         PropTypes.bool,
    positionCalc:   PropTypes.func,
    position:       PropTypes.object,
    positionAt:     PropTypes.string,
    positionMy:     PropTypes.string,
    positionTarget: PropTypes.any,
    zIndex:         PropTypes.number,
    collision:      PropTypes.string,
    onOpen:         PropTypes.func,
    onClose:        PropTypes.func,
    children:       PropTypes.node,
    style:          PropTypes.object,
    className:      PropTypes.string
  };

  constructor(props) {
    super(props);
    this.state = {};
  }

  /**
   * Re-render the dialog content when we get a new set of props
   * @param  {Object} newProps The new props
   * @return {void}
   */
  componentWillReceiveProps(newProps) {
    const { isOpen = false, positionCalc, positionTarget, positionMy, positionAt, collision, zIndex } = newProps;
    const position = positionCalc ? positionCalc() || {} : {};
    const placement = newProps.position ||
      {
        my:        positionMy || 'left top',
        at:        positionAt || 'right bottom',
        of:        null,
        collision: collision || 'none'
      };

    if (positionTarget) {
      placement.of = positionTarget;
      if (!(positionTarget instanceof $) && ReactDOM.findDOMNode(positionTarget) !== null) {
        placement.of = ReactDOM.findDOMNode(positionTarget);
      }

      placement.collision = collision || placement.collision;

      // Error out if we don't have a position target
      if (placement.of === null) {
        console.error('No position target specified');
      }
    }

    this.setState({
      isOpen,
      top:       position.top,
      left:      position.left,
      my:        placement.my,
      at:        placement.at,
      of:        placement.of,
      collision: placement.collision,
      zIndex
    });
  }

  /**
   * Update the position of the component
   * @return {void}
   */
  updatePosition() {
    const $node = $(this.cont || ReactDOM.findDOMNode(this));
    invariant($node.length, 'Positioned expects a DOMNode to be available.');

    $node.css('position', 'absolute');
    const { top, left, my, at, of, collision, zIndex } = this.state;

    if (top) {
      $node.css('top', top).css('left', left)
      ;
    } else {
      $node.position({
        my,
        at,
        of,
        collision
      });
    }

    if (zIndex) {
      $node.css('z-index', zIndex);
    }
  }

  /**
   * Render a fake div in place of the element
   * @return {XML} The rendered element
   */
  render() {
    return <div />;
  }
}
