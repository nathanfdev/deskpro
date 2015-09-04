import React from "react";
import Moment from "moment";
import { DragSource } from "react-dnd";
import { connect } from 'redux/react';
import $ from 'jquery';
import * as TaskActions from "../Actions/TaskListActions";
import { IntlMixin, FormattedDate } from "react-intl";
import Formsy from "formsy-react";
import FRC from "../../../../../Component/FormComponents/main.js";
import DragTypes from "../../../Services/DragTypes.js";
import Picker from "anytime";
import { getEmptyImage } from 'react-dnd/modules/backends/HTML5';

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
  render: function () {
    const {task, connectDragSource} = this.props;
    return connectDragSource(<li><a href="#">{task.title}</a></li>);
  }
})

module.exports = DragSource(DragTypes.TASK, cardSource, (connect, monitor) => ({
  connectDragSource: connect.dragSource(),
  connectDragPreview: connect.dragPreview(),
  isDragging: monitor.isDragging()
}))(TaskCalendarCard);
