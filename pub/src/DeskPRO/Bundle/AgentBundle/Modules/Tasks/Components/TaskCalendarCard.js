import React from 'react';
import Moment from 'moment';
import { DragSource } from 'react-dnd';
import $ from 'jquery';
import DragTypes from '../../../Services/DragTypes.js';

const cardSource = {
  beginDrag(props, monitor, component) {
    const width = $(React.findDOMNode(component)).width();

    return {
      id: props.task.id,
      details: props.task,
      width: width,
      subtype: 'calendar'
    };
  }
};

function collect(connect, monitor) {
  return {
    connectDragSource: connect.dragSource(),
    connectDragPreview: connect.dragPreview()
  };
}

const TaskCalendarCard = React.createClass({
  openHover: function(task) {
    const position = {x: event.x, y: event.y};
    this.props.openHover(task, position);
  },

  render: function() {
    const {task, connectDragSource} = this.props;
    const dueDate = new Moment(task.date_due);
    const overdueClass = dueDate.isBefore() ? 'urgent' : '';
    return connectDragSource(<li className={overdueClass}
      onMouseEnter={this.openHover.bind(this, task)}
      onMouseLeave={this.props.closeHover.bind(this)}>
      <a href="#">{task.title}</a>
    </li>);
  }
});

module.exports = DragSource(DragTypes.TASK, cardSource, (connect, monitor) => ({
  connectDragSource: connect.dragSource(),
  connectDragPreview: connect.dragPreview(),
  isDragging: monitor.isDragging()
}))(TaskCalendarCard);
