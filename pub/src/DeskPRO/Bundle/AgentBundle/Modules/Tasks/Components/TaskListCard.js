import React from "react";
import Moment from "moment";
import { DragSource } from "react-dnd";
import { IntlMixin, FormattedDate } from "react-intl";
import DragTypes from "../../../Services/DragTypes.js";

const listCardSource = {
  beginDrag(props) {
    return { id: props.task.id, dispatch: props.dispatch, source: props.source };
  }
};

const TaskListCard = React.createClass({

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
              <i className="fa fa-calendar-o" /> Due: {this.props.task.date_due ? <FormattedDate
                  value={Date.parse(this.props.task.date_due)}
                  day="numeric"
                  month="long"
                  year="numeric"
              />
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
  isDragging: monitor.isDragging()
}))(TaskListCard);
