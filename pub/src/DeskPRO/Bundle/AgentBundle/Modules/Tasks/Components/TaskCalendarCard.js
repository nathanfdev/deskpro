import React from 'react';
import ReactDOM from 'react-dom';
import Moment from 'moment';
import { DragSource } from 'react-dnd';
import jQuery from 'jquery';
import DragTypes from '../../../Services/DragTypes.js';

const cardSource = {
  beginDrag(props, monitor, component) {
    const width = jQuery(ReactDOM.findDOMNode(component)).width();

    return {
      id: props.task.get('id'),
      details: props.task,
      width: width,
      subtype: 'calendar'
    };
  }
};

const TaskCalendarCard = React.createClass({
  propTypes: {
    closeHover: React.PropTypes.func,
    connectDragSource: React.PropTypes.func,
    openHover: React.PropTypes.func,
    task: React.PropTypes.object
  },

  openHover: function(task, event) {
    const position = {x: event.clientX, y: event.clientY};
    this.props.openHover(task, position);
  },

  render: function() {
    const {task, connectDragSource} = this.props;
    const dueDate = new Moment(task.get('date_due'));
    const overdueClass = dueDate.isBefore() ? 'urgent' : '';

    const response = (<li className={overdueClass}
      onMouseEnter={this.openHover.bind(this, task)}
      onMouseLeave={this.props.closeHover.bind(this)}>
      <a href="#">{task.get('title')}</a>
    </li>);

    if (connectDragSource) {
      return connectDragSource(response);
    }

    return response;
  }
});

module.exports = DragSource(DragTypes.TASK, cardSource, (connect, monitor) => ({
  connectDragSource: connect.dragSource(),
  connectDragPreview: connect.dragPreview(),
  isDragging: monitor.isDragging()
}))(TaskCalendarCard);
// module.exports = TaskCalendarCard;
