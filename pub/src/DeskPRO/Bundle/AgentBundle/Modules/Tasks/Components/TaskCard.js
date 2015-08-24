import React from "react";
import { DragSource } from "react-dnd";
import { connect } from 'redux/react';
import $ from 'jquery';
import * as TaskActions from "../Actions/TaskListActions";
import { IntlMixin, FormattedDate } from "react-intl";
import Formsy from "formsy-react";
import FRC from "../../../../../Component/FormComponents/main.js";
import DragTypes from "../../../Services/DragTypes.js";
import Picker from "anytime";
import Moment from "moment";
import { getEmptyImage } from 'react-dnd/modules/backends/HTML5';

const cardSource = {
  beginDrag(props) {
    return {
      id: props.task.id,
      details: props.task,
      dispatch: props.dispatch,
      source: props.source,
      departments: props.departments,
      teams: props.teams,
      agents: props.agents,
      projects: props.projects
    };
  }
};

function collect(connect, monitor) {
  return {
    connectDragSource: connect.dragSource(),
    connectDragPreview: connect.dragPreview()
  };
}

const TaskCard = React.createClass({
  mixins: [
    require('react-onclickoutside')
  ],

  handleClickOutside: function(evt) {
    if (this.state.editing === true) {
      this.setState({
        editing: false
      });

      let task = this.state.task;
      task.taskId = this.props.task.id;

      this.props.editTask(this.props.source, task);
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

  handleTitleChange: function (name, value) {
    let task = this.state.task;
    task.title = value;
    this.setState({
      task: task
    });
  },

  handleAssigneeChange: function(value) {
    let task = this.state.task;
    const assignment = value.target.value;

    task.agents = [];
    task.teams = [];
    task.departments = [];

    if (assignment !== 'unassigned') {
      let assignmentParts = assignment.split('-');
      task[assignmentParts[0]] = [assignmentParts[1]];
    }

    this.setState({
      task: task
    });

    task.taskId = this.props.task.id;

    this.props.editTask(this.props.source, task);
  },

  toggleMassAction: function(event) {
    this.props.updateMassActions(this.props.task.id);
  },

  componentDidMount() {
    const dueField = "due-" + this.props.task.id;

    // Check if the due field actually exists before we try and add a date picker (e.g. on done tasks)
    if (typeof (this.refs[dueField]) !== 'undefined') {
      const dueButton = "due-button-" + this.props.task.id;

      // Get the date for the task, and format it nicely
      const initial = this.props.task.date_due ? Moment(this.props.task.date_due).format() : null;

      // Create the picker
      let picker = new Picker({
        input: React.findDOMNode(this.refs[dueField]),
        button: React.findDOMNode(this.refs[dueButton]),
        initialValue: initial,
        format: "hh:mm, MMMM D, YYYY"
      });
      picker.render();

      // Change the component state and submit the edit when the date is changed
      picker.on('change', (newDate) => {
        let task = this.state.task;
        task.date_due = newDate ? Moment(newDate).format() : null;
        this.setState({
          task: task
        });

        picker.updateInput();
        task.taskId = this.props.task.id;

        this.props.editTask(this.props.source, task);
      });
    }

    this.props.connectDragPreview(getEmptyImage(), {
      // IE fallback: specify that we'd rather screenshot the node
      // when it already knows it's being dragged so we can hide it with CSS.
      captureDraggingState: true
    });
  },

  getStyles: function(props) {
    const { isDragging } = props;

    return {
      // IE fallback: hide the real node using CSS when dragging
      // because IE will ignore our custom "empty image" drag preview.
      opacity: isDragging ? 0 : 1,
      height: isDragging ? 0 : ''
    };
  },

  render: function () {
    const { task, projects, linked_items, departments, teams, agents, source, connectDragSource } = this.props;

    const selected = this.props.selected;

    let cardClass = task.is_done ? "card task-card task-card-completed" : "card task-card";
    let detailsButtonText = this.state.expanded ? "Collapse" : "Expand";

    let doneButton = task.is_done ? <span>Done <i className="fa fa-check" /></span> : "Mark Done";

    let ticket_link = undefined;
    let ticket_title = 'Linked ticket';

    if (task.linked_items.length > 0) {
      task.linked_items.forEach((item) => {
        if (typeof linked_items[item].ticket !== 'undefined' && linked_items[item].ticket !== null) {
          ticket_link = '#' + linked_items[item].ticket;
          ticket_title = this.props.tickets[linked_items[item].ticket].subject;
        }
      });
    }

    let assigneeId = "unassigned";

    if (task.agents.length > 0) {
      // We assume one assignment for now, though we will need to support more later
      const agentId = task.agents[0];
      assigneeId = "agents-" + agentId;
    } else if (task.teams.length > 0) {
      const teamId = task.teams[0];
      assigneeId = "teams-" + teamId;
    } else if (task.departments.length > 0) {
      const departmentId = task.departments[0];
      assigneeId = "departments-" + departmentId;
    }

    const dueField = "due-" + task.id;
    const dueButton = "due-button-" + task.id;

    const overdue = Moment(task.date_due).isBefore();

    return connectDragSource(<div className={cardClass} key={task.id} style={this.getStyles(this.props)}>
        <div>
          <div className="card-status-bar status-bar-left" />
          <div className="card-status-bar status-bar-right" />

          <div className="card-checkbox">
            <span className="checkbox" onClick={this.toggleMassAction}>
              {selected ? <i className="fa fa-check" /> : '' }
            </span>
          </div>

            <div className="top-right-box">
              {!task.is_done ?
                <span className="assignment">
                  <select name="assigned" id="assigned" value={assigneeId} onChange={this.handleAssigneeChange}>
                    <option value="unassigned">Unassigned</option>
                    { agents ? <optgroup label="Agents">
                      { Object.keys(agents).map((key) => {
                        let agentId = "agents-" + key;
                        return <option key={agentId} value={agentId}>{agents[key].name}</option>;
                      })}
                    </optgroup> : '' }
                    { teams ? <optgroup label="Teams">
                      { Object.keys(teams).map((key) => {
                        let teamId = "teams-" + key;
                        return <option key={teamId} value={teamId}>{teams[key].name}</option>;
                      })}
                    </optgroup> : '' }
                    { departments ? <optgroup label="Departments">
                      { Object.keys(departments).map((key) => {
                        let departmentId = "departments-" + key;
                        return <option key={departmentId} value={departmentId}>{departments[key].title}</option>;
                      })}
                    </optgroup> : '' }
                  </select>
                </span>:
                <button className="task-details-button" onClick={this.toggleDetails}>{detailsButtonText} <i
                  className="fa fa-bars"/></button>}
            </div>

          <div className="card-line">
              <span className="line-box card-task-mark" onClick={this.props.toggleDone.bind(this, task, source)}>
                {doneButton}
              </span>

            { !this.state.editing ?
            <h1 onDoubleClick={this.editMode}>{task.title}</h1> :
              <Formsy.Form className="inline-form">
              <h1><FRC.Input type="text" name="title" value={task.title} onChange={this.handleTitleChange} /></h1>
              </Formsy.Form>
            }
          </div>

          { this.state.expanded || !task.is_done ?
            <div className="card-line task-details">
              <div className="task-extras">
                <div>{task.comment_count} <i className="fa fa-comment"/></div>

                {task.subtasks_total > 0 ?
                  <span><span className="disc" />
            <div className="subtask-count">{task.subtasks_done}/{task.subtasks_total} <i className="fa fa-folder-open"/>
            </div></span> : ''}
              </div>

              <div className="task-properties">
                <div className={overdue ? "overdue" : ""} ref={dueButton} >

                  <i className="fa fa-calendar-o" /> Due: {task.date_due ? <FormattedDate
                  value={Date.parse(task.date_due)}
                  day="numeric"
                  month="long"
                  year="numeric"
                  />
                  : 'N/A' }
                  <input type="text" name="due-date" className="due-date-field" ref={dueField} disabled="disabled" />
                </div>

                {task.project && projects[task.project] ? <span>
                  <span className="disc" /><i className="fa fa-book"/> {projects[task.project].title}
                </span> : ''}

                {ticket_link ? <span>
                <span className="disc"></span>
                  <i className="fa fa-link"/><a href={ticket_link}>{ticket_title}</a>
                </span> : ''}
              </div>
            </div> : '' }
        </div>
    </div>);
  }
});

module.exports = DragSource(DragTypes.TASK, cardSource, (connect, monitor) => ({
  connectDragSource: connect.dragSource(),
  connectDragPreview: connect.dragPreview(),
  isDragging: monitor.isDragging()
}))(TaskCard);
