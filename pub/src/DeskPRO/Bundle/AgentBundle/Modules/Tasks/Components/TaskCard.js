import React from "react";
import { DragSource } from "react-dnd"
import { connect } from 'redux/react';
import $ from 'jquery';
import * as TaskActions from "../Actions/TaskListActions";
import { IntlMixin, FormattedDate } from "react-intl";
import Formsy from "formsy-react";
import FRC from "../../../../../Component/FormComponents/main.js";
import DragTypes from "../../../Services/DragTypes.js";

const cardSource = {
  beginDrag(props) {
    return { id: props.task.id, dispatch: props.dispatch, source: props.source };
  }
};

const TaskCard = React.createClass({
  mixins: [
    require('react-onclickoutside')
  ],

  handleClickOutside: function(evt) {
    if (this.state.editing === true) {
      this.setState({
        editing: false
      });
      //this.props.editTask(this.props.source, this.state.task);
    }
  },

  getInitialState: function() {
    return {
      expanded: false,
      editing: false,
      task: {}
    };
  },

  toggleDetails: function () {
    this.setState({
      expanded: !this.state.expanded
    });
  },

  editMode: function () {
    this.setState({
      editing: true
    });
  },

  handleTitleChange: function (name, value, test) {
    //let task = this.state.task;
    //task.title = value;
    //this.setState({
    //  task: task
    //});
  },

  render: function () {
    const { task, projects, linked_items, departments, teams, agents, source, connectDragSource, isDragging } = this.props;

    let cardClass = task.is_done ? "card task-card task-card-completed" : "card task-card";
    let detailsButtonText = this.state.expanded ? "Collapse" : "Expand";

    let doneButton = task.is_done ? <span>Done <i className="fa fa-check" /></span> : "Mark Done";

    let ticket_link = undefined;

    if (task.linked_items.length > 0) {
      task.linked_items.forEach((item) => {
        if (typeof linked_items[item].ticket !== 'undefined' && linked_items[item].ticket !== null) {
          ticket_link = '#' + linked_items[item].ticket;
        }
      });
    }

    let assignee = undefined;

    if (task.agents.length > 0) {
      // We assume one assignment for now, though we will need to support more later
      const agentId = task.agents[0];
      let agent = agents[agentId];

      assignee = <span><span className="text">{agent.name}</span> <span className="chat-avatar"
                                                                        style={{backgroundImage: "url(./img/avatar6.png)"}} /></span>
    } else if (task.teams.length > 0) {
      const teamId = task.teams[0];
      let team = teams[teamId];
      assignee = <span><span className="text">{team.name}</span></span>
    } else if (task.departments.length > 0) {
      const departmentId = task.departments[0];
      let department = departments[departmentId];
      assignee = <span><span className="text">{department.title}</span></span>
    }

    return connectDragSource(<div className={cardClass} key={task.id} onDoubleClick={this.editMode}>
      { !this.state.editing ?
        <div>
          <div className="card-status-bar status-bar-left"></div>
          <div className="card-status-bar status-bar-right"></div>

          <div className="card-checkbox">
            <span className="checkbox"></span>
          </div>

          {assignee || task.is_done ?
            <div className="top-right-box">
              {!task.is_done ? assignee :
                <button className="task-details-button" onClick={this.toggleDetails}>{detailsButtonText} <i
                  className="fa fa-bars"/></button>}
            </div> : ''}

          <div className="card-line">
              <span className="line-box card-task-mark" onClick={this.props.toggleDone.bind(this, task, source)}>
                {doneButton}
              </span>

            <h1>{task.title}</h1>
          </div>

          { this.state.expanded || !task.is_done ?
            <div className="card-line task-details">
              <div className="task-extras">
                <div>{task.comment_count} <i className="fa fa-comment"/></div>

                {task.subtasks_total > 0 ?
                  <span><span className="disc"></span>
            <div className="subtask-count">{task.subtasks_done}/{task.subtasks_total} <i className="fa fa-folder-open"/>
            </div></span> : ''}
              </div>

              <div className="task-properties">
                <div>
                  <i className="fa fa-calendar-o"/> Due: {task.date_due ? <FormattedDate
                  value={Date.parse(task.date_due)}
                  day="numeric"
                  month="long"
                  year="numeric"/> : 'N/A' }
                </div>

                {task.project && projects[task.project] ? <span>
                  <span className="disc"></span><i className="fa fa-book"/> {projects[task.project].title}
                </span> : ''}

                {ticket_link ? <span>
                <span className="disc"></span>
                  <i className="fa fa-link"/><a href={ticket_link}>Linked ticket</a>
                </span> : ''}
              </div>
            </div> : '' }
        </div> :
        <Formsy.Form>
          <div className="card-line">
            <h1><FRC.Input type="text" name="title" value={task.title} onChange={this.handleTitleChange('Test string')} /></h1>
          </div>
        </Formsy.Form>
      }
    </div>);
  }
});

module.exports = DragSource(DragTypes.TASK, cardSource, (connect, monitor) => ({
  connectDragSource: connect.dragSource(),
  isDragging: monitor.isDragging()
}))(TaskCard);