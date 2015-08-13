import React from "react";
import { connect } from 'react-redux';
import $ from 'jquery';
import * as TaskActions from "../Actions/TaskListActions";
import { IntlMixin, FormattedDate } from "react-intl";
import Formsy from "formsy-react";
import FRC from "../../../../../Component/FormComponents/main.js";

@connect(state => ({
  taskFrameList: state.Tasks.taskFrameList
}))
export default class TasksListFrame extends React.Component {
  constructor(props) {
    super(props);

    this.intl = IntlMixin;
  }

  toggleDone(object, reload) {
    object.is_done = !object.is_done;

    let newValues = {
      taskId : object.id,
      is_done : object.is_done
    };

    if (newValues.is_done === true) {
      newValues['percent_complete'] = 100;
    }

    this.forceUpdate();

    this.props.dispatch(TaskActions.editTask(newValues, reload));
  }

  createTask(source, model) {
    this.props.dispatch(TaskActions.createTask({
      title : model.title
    }, source));
  }

  render() {
    const {taskFrameList} = this.props;
    const _this = this;
    let projects = {};
    let linked_items = {};
    let departments = {};
    let teams = {};
    let agents = {};

    // Attach IDs to the projects
    if (taskFrameList.taskFrameProjects && typeof taskFrameList.taskFrameProjects.forEach === 'function') {
      taskFrameList.taskFrameProjects.forEach((project) => {
        projects[project.id.toString()] = project;
      });
    }

    // Attach IDs to the linked item
    if (taskFrameList.taskFrameLinks && typeof taskFrameList.taskFrameLinks.forEach === 'function') {
      taskFrameList.taskFrameLinks.forEach((link) => {
        linked_items[link.id.toString()] = link;
      });
    }

    // Attach assignments
    if (taskFrameList.taskFrameAgents && typeof taskFrameList.taskFrameAgents.forEach === 'function') {
      taskFrameList.taskFrameAgents.forEach((agent) => {
        agents[agent.id.toString()] = agent;
      });
    }
    if (taskFrameList.taskFrameTeams && typeof taskFrameList.taskFrameTeams.forEach === 'function') {
      taskFrameList.taskFrameTeams.forEach((team) => {
        teams[team.id.toString()] = team;
      });
    }
    if (taskFrameList.taskFrameDepartments && typeof taskFrameList.taskFrameDepartments.forEach === 'function') {
      taskFrameList.taskFrameDepartments.forEach((department) => {
        departments[department.id.toString()] = department;
      });
    }

    return (
      <section className="task-list-frame dp-list-frame">
      <div className="ticket-list">

        <div className="tickets-control-bar">

          <div className="bulk-edit-control">
            <a href="#">
              <span className="checkbox"><i className="fa fa-check" /></span>
            </a>
            <span className="count" style={{display: "none"}}><span>14</span></span>
          </div>

          <span className="ticket-controls-default">
            <a href="#" className="ticket-control-button">
              <span className="title">Order by:</span>
              <span className="focus">Date</span>
              <span className="down">Asc <i className="fa fa-caret-down" /></span>
            </a>

            <a href="#" className="ticket-control-button">
              <span className="title">Filter by:</span>
              <span className="focus">12</span>
              <span className="down">Completed <i className="fa fa-caret-down" /></span>
            </a>

            <a href="#" className="ticket-control-button">
              <span className="title">View:</span>
              <span className="multi">
                List
                <span className="multi-down"><i className="fa fa-caret-down" /></span>
              </span>
            </a>
          </span>

          <span className="ticket-controls-bulk-editing">
            <a href="#">
              <span>Assign</span>
              <hr />
              <i className="fa fa-caret-down" />
            </a>

            <a href="#">
              <span>Statuses</span>
              <hr />
              <i className="fa fa-caret-down" />
            </a>

            <a href="#">
              <span>Macros</span>
              <hr />
              <i className="fa fa-caret-down" />
            </a>

            <a href="#">
              <span><i className="fa fa-reply" /></span>
              <hr />
              <i className="fa fa-caret-down" />
            </a>

            <a href="#">
              <span><i className="fa fa-asterisk" /></span>
              <hr />
              <i className="fa fa-caret-down" />
            </a>

            <hr />

            <a href="#" className="active">
              <span>GO</span>
            </a>

            <a href="#" className="cancel">
              <span>Cancel</span>
            </a>
          </span>
        </div>

        <Formsy.Form onSubmit={_this.createTask.bind(_this, taskFrameList.taskFrameSource)}>
          <FRC.Input name="title" type="text" />
          <button type="submit" value="Save" className="button">Add</button>
        </Formsy.Form>

        {taskFrameList.taskFrameList ? taskFrameList.taskFrameList.map((object) => {
          let cardClass = object.is_done ? "card task-card task-card-completed" : "card task-card";
          let doneButton = object.is_done ? <span>Done <i className="fa fa-check" /></span> : "Mark Done";

          let ticket_link = undefined;

          if (object.linked_items.length > 0) {
            object.linked_items.forEach((item) => {
              if (typeof linked_items[item].ticket !== 'undefined' && linked_items[item].ticket !== null) {
                ticket_link = '#' + linked_items[item].ticket;
              }
            });
          }

          let assignee = undefined;

          if (object.agents.length > 0) {
            // We assume one assignment for now, though we will need to support more later
            const agentId = object.agents[0];
            let agent = agents[agentId];

            assignee = <span><span className="text">{agent.name}</span> <span className="chat-avatar"
                                                                              style={{backgroundImage: "url(./img/avatar6.png)"}} /></span>
          } else if (object.teams.length > 0) {
            const teamId = object.teams[0];
            let team = teams[teamId];
            assignee = <span><span className="text">{team.name}</span></span>
          } else if (object.departments.length > 0) {
            const departmentId = object.departments[0];
            let department = departments[departmentId];
            assignee = <span><span className="text">{department.title}</span></span>
          }

          return <div className={cardClass} key={object.id}>
            <div className="card-status-bar status-bar-left"></div>
            <div className="card-status-bar status-bar-right"></div>

            <div className="card-checkbox">
              <span className="checkbox"><i className="fa fa-check"/></span>
            </div>

            {assignee ?
            <div className="top-right-box">
              {assignee}
            </div> : ''}

            <div className="card-line">
              <span className="line-box card-task-mark" onClick={_this.toggleDone.bind(_this, object, taskFrameList.taskFrameSource)}>
                {doneButton}
              </span>

              <h1>{object.title}</h1>
            </div>

            <div className="card-line">
              <div className="task-extras">
                <div>5 <i className="fa fa-comment"/></div>
                <span className="disc"></span>

                <div>1/3 <i className="fa fa-folder-open"/></div>
              </div>

              <div className="task-properties">
                <div>
                  <i className="fa fa-calendar-o"/> Due: {object.date_due ? <FormattedDate
                  value={Date.parse(object.date_due)}
                  day="numeric"
                  month="long"
                  year="numeric" /> : 'N/A' }
                </div>

                {object.project && projects[object.project] ? <span>
                  <span className="disc"></span><i className="fa fa-book"/> {projects[object.project].title}
                </span> : ''}

                {ticket_link ? <span>
                <span className="disc"></span>
                  <i className="fa fa-link"/><a href={ticket_link}>Linked ticket</a>
                </span> : ''}
              </div>
            </div>
          </div>
        }) : '' }
      </div>
    </section>
    );
  }
}
