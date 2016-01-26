import React, { Component, PropTypes } from 'react';
import jQuery from 'jquery';
import { findDOMNode } from 'react-dom';
import classNames from 'classnames';
import { DragSource, DropTarget } from 'react-dnd';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';


export const cardSource = {
  beginDrag({ value }, {}, component) {
    console.log('HERE WE GO', value);
    return {
      id: value,
      width: jQuery(findDOMNode(component)).width()
    };
  },
  canDrag({ editing, updateData = {} }) {
    return !editing && !updateData.date_created && !updateData.date_done;
  }
};

export const cardSourceCollect = (dragConnect, monitor) => ({
  connectDragSource: dragConnect.dragSource(),
  connectDragPreview: dragConnect.dragPreview(),
  isDragging: monitor.isDragging()
});

export const cardTarget = {
  hover(props, monitor, component) {
    const dragIndex = monitor.getItem().index;
    const hoverIndex = props.index;

    // Don't replace items with themselves
    if (dragIndex === hoverIndex) {
      return;
    }

    // Determine rectangle on screen
    const hoverBoundingRect = findDOMNode(component).getBoundingClientRect();

    // Get vertical middle
    const hoverMiddleY = (hoverBoundingRect.bottom - hoverBoundingRect.top) / 2;

    // Determine mouse position
    const clientOffset = monitor.getClientOffset();

    // Get pixels to the top
    const hoverClientY = clientOffset.y - hoverBoundingRect.top;

    // Only perform the move when the mouse has crossed half of the items height
    // When dragging downwards, only move when the cursor is below 50%
    // When dragging upwards, only move when the cursor is above 50%

    // Dragging downwards
    if (dragIndex < hoverIndex && hoverClientY < hoverMiddleY) {
      return;
    }

    // Dragging upwards
    if (dragIndex > hoverIndex && hoverClientY > hoverMiddleY) {
      return;
    }

    // Time to actually perform the action
    props.moveCard(dragIndex, hoverIndex);

    // Note: we're mutating the monitor item here!
    // Generally it's better to avoid mutations,
    // but it's good here for the sake of performance
    // to avoid expensive index searches.
    monitor.getItem().index = hoverIndex;
  }
};

export const targetCollect = (dragConnect, monitor) => ({
  connectDropTarget: dragConnect.dropTarget(),
  isOver: monitor.isOver()
});

@DragSource(constants.DRAGGABLE_TYPE_FIELD, cardSource, cardSourceCollect)
@DropTarget(constants.DRAGGABLE_TYPE_FIELD, cardTarget, targetCollect)

export class ViewField extends Component {
  /**
   * The valid PropTypes for this component
   * @type {Object}
   */
  static propTypes = {
    fixed: PropTypes.bool,
    isShown: PropTypes.any,
    changeState: PropTypes.func,
    value: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    connectDragSource: PropTypes.func.isRequired,
    connectDropTarget: PropTypes.func.isRequired,
    isDragging: PropTypes.bool.isRequired,
    moveCard: PropTypes.func.isRequired
  };

  clickHandle(event) {
    event.preventDefault();
    const { isShown, changeState, value } = this.props;
    if (changeState) {
      changeState(value, !isShown);
    }
  }

  renderStatus() {
    const style = {};
    if (!this.props.isShown) {
      style.display = 'none';
    }

    return (
      <span className="dpw-navigation-dropdown-column-list-status" style={style}>
        <i className="fa fa-check"></i>
      </span>
    );
  }

  render() {
    const { isDragging, connectDragSource, connectDropTarget, label, fixed } = this.props;
    const anchorClasses = classNames('dpw-navigation-dropdown-column-list-item', {
      'dpw-navigation-dropdown-item-disabled': fixed
    });
    const moveIconClass = classNames('fa', { 'fa-minus': fixed, 'fa-navicon': !fixed });

    return connectDragSource(connectDropTarget(
      <li className={classNames({ 'dragging-item': isDragging })} onClick={this.clickHandle.bind(this)}>
        <a className={anchorClasses} href="#">
          {this.renderStatus()}
          <span className="dpw-navigation-dropdown-column-list-move">
            <i className={moveIconClass}></i>
          </span>
          <span className="dpw-navigation-dropdown-column-list-title">{label}</span>
        </a>
      </li>
    ));
  }
}
