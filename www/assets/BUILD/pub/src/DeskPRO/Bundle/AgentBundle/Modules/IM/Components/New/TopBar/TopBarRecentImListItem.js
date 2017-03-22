import React, { PropTypes } from 'react';
import { findDOMNode } from 'react-dom';
import { DragSource, DropTarget } from 'react-dnd';

const itemSource = {
  beginDrag(props) {
    props.toggleTooltips(false);

    return {
      id:    props.chat.get('id'),
      order: props.chat.get('order')
    };
  },

  endDrag(props) {
    props.toggleTooltips(true);
  }
};

const itemTarget = {
  hover(props, monitor, component) {
    const dragId = monitor.getItem().id;
    const hoverId = props.chat.get('id');
    const dragOrder = monitor.getItem().order;
    const hoverOrder = props.chat.get('order');

    if (dragId === hoverId) {
      return;
    }
    const hoverBoundingRect = findDOMNode(component).getBoundingClientRect(); // eslint-disable-line
    const hoverMiddleY = (hoverBoundingRect.bottom - hoverBoundingRect.top) / 2;
    const clientOffset = monitor.getClientOffset();
    const hoverClientY = clientOffset.y - hoverBoundingRect.top;

    if (dragOrder < hoverOrder && hoverClientY < hoverMiddleY) {
      return;
    }

    if (dragOrder > hoverOrder && hoverClientY > hoverMiddleY) {
      return;
    }

    props.swapHeads(dragId, hoverId, dragOrder, hoverOrder);

    monitor.getItem().order = hoverOrder;
  },

  drop(props, monitor) {
    const dragId = monitor.getItem().id;
    const hoverId = props.chat.get('id');
    const dragOrder = monitor.getItem().order;
    const hoverOrder = props.chat.get('order');

    props.swapHeads(dragId, hoverId, dragOrder, hoverOrder, true);
  }
};

@DropTarget('head', itemTarget, connect => ({
  connectDropTarget: connect.dropTarget()
}))
@DragSource('head', itemSource, (connect, monitor) => ({
  connectDragSource: connect.dragSource(),
  isDragging:        monitor.isDragging()
}))
export default class TopBarRecentImListItem extends React.Component {

  static propTypes = {
    className:         PropTypes.string.isRequired,
    chat:              PropTypes.string.isRequired,
    children:          PropTypes.oneOfType([PropTypes.object, PropTypes.array]),
    onClick:           PropTypes.func.isRequired,
    connectDragSource: PropTypes.func.isRequired,
    connectDropTarget: PropTypes.func.isRequired
  };

  render() {
    const { children, className, chat, onClick, connectDragSource, connectDropTarget } = this.props;

    return connectDragSource(connectDropTarget(
      <span
        className={className}
        id={`chat-${chat.get('id')}`}
        onClick={onClick}
      >
        {children}
      </span>
    ));
  }
}
