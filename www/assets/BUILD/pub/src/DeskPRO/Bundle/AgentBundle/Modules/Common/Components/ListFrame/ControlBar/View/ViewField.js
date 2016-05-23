import React, { Component, PropTypes } from 'react';
import jQuery from 'jquery';
import { findDOMNode } from 'react-dom';
import classNames from 'classnames';
import { DragSource, DropTarget } from 'react-dnd';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { getEmptyImage } from 'react-dnd-html5-backend';

export const cardSource = {
  beginDrag({ index, type, onChangeDisplayOrder }, {}, component) {
    return {
      index,
      type,
      onChangeDisplayOrder,
      width: jQuery(findDOMNode(component)).width()
    };
  }
};

export const cardSourceCollect = (dragConnect, monitor) => ({
  connectDragSource:  dragConnect.dragSource(),
  isDragging:         monitor.isDragging()
});

export const cardTarget = {
  drop({ index, type }, monitor) {
    const item = monitor.getItem();
    if (item.onChangeDisplayOrder) {
      item.onChangeDisplayOrder(item.index, index);
    }
  },
  canDrop({ index, type }, monitor) {
    return type === (monitor.getItem() || {}).type;
  }
};

export const targetCollect = (dragConnect, monitor) => ({
  connectDropTarget: dragConnect.dropTarget(),
  isOver:            monitor.isOver()
});

@DragSource(constants.DRAGGABLE_TYPE_FIELD, cardSource, cardSourceCollect)
@DropTarget(constants.DRAGGABLE_TYPE_FIELD, cardTarget, targetCollect)

export class ViewField extends Component {
  /**
   * The valid PropTypes for this component
   * @type {Object}
   */
  static propTypes = {
    fixed:              PropTypes.bool,
    isShown:            PropTypes.any,
    changeState:        PropTypes.func,
    value:              PropTypes.string.isRequired,
    label:              PropTypes.string.isRequired,
    connectDragSource:  PropTypes.func.isRequired,
    connectDropTarget:  PropTypes.func.isRequired,
    isDragging:         PropTypes.bool.isRequired
  };

  clickHandle = (event) => {
    event.preventDefault();
    const { isShown, changeState, value } = this.props;
    if (changeState) {
      changeState(value, !isShown);
    }
  };

  renderStatus() {
    if (!this.props.isShown) {
      return null;
    }

    return (
      <span className="dpw-navigation-dropdown-column-list-status">
        <i className="fa fa-check" />
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
      <li className={classNames({ 'dragging-item': isDragging })} onClick={this.clickHandle}>
        <a className={anchorClasses}>
          {this.renderStatus()}
          <span className="dpw-navigation-dropdown-column-list-move">
            <i className={moveIconClass} />
          </span>
          <span className="dpw-navigation-dropdown-column-list-title">{label}</span>
        </a>
      </li>
    ));
  }
}
