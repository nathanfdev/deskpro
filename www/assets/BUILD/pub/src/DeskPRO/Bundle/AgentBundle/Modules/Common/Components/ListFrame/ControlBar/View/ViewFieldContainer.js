import PropTypes from 'prop-types';
import React, { Component } from 'react';
import jQuery from 'jquery';
import { findDOMNode } from 'react-dom';
import { DragSource, DropTarget } from 'react-dnd';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ViewField } from './ViewField';

export const cardSource = {
  beginDrag({ index, type, changeOrder }, {}, component) {
    return { index, type, changeOrder, width: jQuery(findDOMNode(component)).width() };
  }
};

export const cardSourceCollect = (dragConnect, monitor) => ({
  connectDragSource: dragConnect.dragSource(),
  isDragging:        monitor.isDragging()
});

export const cardTarget = {
  drop({ index }, monitor) {
    const item = monitor.getItem();
    if (!item.changeOrder) return;
    item.changeOrder(item.index, index);
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

export class ViewFieldContainer extends Component {
  render() {
    return <ViewField {...this.props} />;
  }
}
