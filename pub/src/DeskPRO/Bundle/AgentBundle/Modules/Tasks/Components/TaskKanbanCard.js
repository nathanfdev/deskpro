import React from "react";
import Moment from "moment";
import { DragSource } from "react-dnd";
import { IntlMixin, FormattedDate } from "react-intl";
import DragTypes from "../../../Services/DragTypes.js";
import $ from "jquery";
import { getEmptyImage } from 'react-dnd/modules/backends/HTML5';

const listCardSource = {
  beginDrag(props, monitor, component) {
    const width = $(React.findDOMNode(component)).width();

    return {
      id: props.task.id,
      details: props.task,
      dispatch: props.dispatch,
      source: props.source,
      departments: props.departments,
      teams: props.teams,
      agents: props.agents,
      projects: props.projects,
      tickets: props.tickets,
      width: width,
      subtype: 'kanban'
    };
  }
};

const TaskKanbanCard = React.createClass({

  componentDidMount: function() {
    this.props.connectDragPreview(getEmptyImage(), {
      // IE fallback: specify that we'd rather screenshot the node
      // when it already knows it's being dragged so we can hide it with CSS.
      captureDraggingState: true
    });
  },

  render: function() {
    let assigneeName = '';

    if (this.props.task.agents && this.props.task.agents.length > 0) {
      assigneeName = this.props.agents[this.props.task.agents[0]].name;
    } else if (this.props.task.teams && this.props.task.teams.length > 0) {
      assigneeName = this.props.teams[this.props.task.teams[0]].name;
    } else if (this.props.task.departments && this.props.task.departments.length > 0) {
      assigneeName = this.props.departments[this.props.task.departments[0]].title;
    }

    return this.props.connectDragSource(<div className="card task-card">
      <div>
        <div className="card-status-bar status-bar-left" />
        <div className="card-status-bar status-bar-right" />

        <div className="card-checkbox">
          <span className="checkbox" />
        </div>

        <div className="content">
          <h1>{this.props.task.title}</h1>

          <div className="card-line task-details">
            <div className="top-right-box">
              <span className="assignment">
                {assigneeName}
              </span>
            </div>
            <div>
              <i className="fa fa-calendar-o" /> Due: {this.props.task.date_due ? Moment(this.props.task.date_due).local().format('MMMM D, YYYY')
              : 'N/A' }
            </div>
          </div>
          <hr/>
          <div className="card-line task-properties">
            <span>{this.props.task.comment_count} <i className="fa fa-comment"/></span>

            {this.props.task.subtasks_total > 0 ?
              <span>
                <span className="disc"/>
                <div className="subtask-count">{this.props.task.subtasks_done}/{this.props.task.subtasks_total} <i className="fa fa-folder-open"/></div>
              </span>
            : ''}
          </div>
        </div>
      </div>
    </div>);
  }
});

module.exports = DragSource(DragTypes.TASK, listCardSource, (connect, monitor) => ({
  connectDragSource: connect.dragSource(),
  connectDragPreview: connect.dragPreview(),
  isDragging: monitor.isDragging()
}))(TaskKanbanCard);
