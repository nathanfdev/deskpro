import React from "react";
import { DragSource } from "react-dnd";
import { connect } from 'redux/react';
import $ from 'jquery';
import * as TaskActions from "../Actions/TaskListActions";
import { IntlMixin, FormattedDate } from "react-intl";
import Formsy from "formsy-react";
import FRC from "../../../../../Component/FormComponents/main.js";
import TaskCard from "../Components/TaskCard";
import Moment from "moment";

@connect(state => ({
  taskFrameList: state.taskFrameList
}))
export default class TasksListFrame extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      actionable: []
    };
    this.intl = IntlMixin;
    this.lastGrouping = '';
    this.agents = {};
    this.teams = {};
    this.departments = {};
    this.projects = {};
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

  editTask(source, model) {
    this.props.dispatch(TaskActions.editTask(model, source));
  }

  createTask(source, model) {
    this.props.dispatch(TaskActions.createTask({
      title : model.title
    }, source));
  }

  toggleAllMassActions() {
    if (this.state.actionable.length === this.props.taskFrameList.taskFrameList.length) {
      this.hideMassActionControls();
    } else {
      let actionable = [];

      this.props.taskFrameList.taskFrameList.map((object) => {
        actionable.push(object.id);
      });

      this.setState({
        actionable: actionable
      });
      this.showMassActionControls();
    }
  }

  updateMassActions(taskId) {
    let actionable = this.state.actionable;
    const actionableIndex = actionable.indexOf(taskId);
    if (actionableIndex === -1) {
      actionable.push(taskId);
    } else {
       actionable.splice(actionableIndex, 1);
    }

    this.setState({
      actionable: actionable
    });

    if (actionable.length > 0) {
      this.showMassActionControls();
    } else {
      this.hideMassActionControls()
    }
  }

  showMassActionControls()
  {
    $('.ticket-controls-bulk-editing').animate({"left": '22px'});
  }

  hideMassActionControls()
  {
    this.setState({
      actionable: []
    });
    $('.ticket-controls-bulk-editing').animate({"left": '100%'});
  }

  dividerRequired(object, grouping = false, assignee = false)
  {
    let results = false;

    switch(grouping) {
      case 'due':
        results = this.getDueDividers(object);
        break;
      case 'created':
        results = this.getCreatedDividers(object);
        break;
      case 'assignee':
        results = this.getAssigneeDividers(object);
        break;
      case 'project':
        results = this.getProjectDividers(object);
        break;
      default:
        return false;
    }

    if (results.objectDivider !== this.lastGrouping) {
      this.lastGrouping = results.objectDivider;
      return results.textDisplay;
    }

    return false;
  }

  getDueDividers(object)
  {
    let objectDivider = false;
    let textDisplay = false;

    switch(true) {
      case (Moment(object.date_due).isBefore()):
        objectDivider = 'overdue';
        textDisplay = 'Overdue';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('hour'))):
        objectDivider = 'hour';
        textDisplay = 'This Hour';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('day'))):
        objectDivider = 'day';
        textDisplay = 'Today';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('day').add(1, 'd'))):
        objectDivider = 'tomorrow';
        textDisplay = 'Tomorrow';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('week'))):
        objectDivider = 'week';
        textDisplay = 'This Week';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('week').add(7, 'd'))):
        objectDivider = 'nextweek';
        textDisplay = 'Next Week';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('month'))):
        objectDivider = 'month';
        textDisplay = 'This Month';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('month').add(1, 'M'))):
        objectDivider = 'nextmonth';
        textDisplay = 'Next Month';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('year'))):
        objectDivider = 'year';
        textDisplay = 'This Year';
        break;
      default:
        objectDivider = 'forever';
        textDisplay = 'Other';
        break;
    }

    return {
      objectDivider: objectDivider,
      textDisplay: textDisplay
    };
  }

  getCreatedDividers(object)
  {
    let objectDivider = false;
    let textDisplay = false;
    
    switch(true) {
      case (Moment(object.date_created).isAfter(Moment().startOf('hour'))):
        objectDivider = 'hour';
        textDisplay = 'This Hour';
        break;
      case (Moment(object.date_created).isAfter(Moment().startOf('day'))):
        objectDivider = 'day';
        textDisplay = 'Today';
        break;
      case (Moment(object.date_created).isAfter(Moment().startOf('day').subtract(1, 'd'))):
        objectDivider = 'tomorrow';
        textDisplay = 'Yesterday';
        break;
      case (Moment(object.date_created).isAfter(Moment().startOf('week'))):
        objectDivider = 'week';
        textDisplay = 'This Week';
        break;
      case (Moment(object.date_created).isAfter(Moment().startOf('week').subtract(7, 'd'))):
        objectDivider = 'lastweek';
        textDisplay = 'Last Week';
        break;
      case (Moment(object.date_created).isAfter(Moment().startOf('month'))):
        objectDivider = 'month';
        textDisplay = 'This Month';
        break;
      case (Moment(object.date_created).isAfter(Moment().startOf('month').subtract(1, 'M'))):
        objectDivider = 'lastmonth';
        textDisplay = 'Last Month';
        break;
      case (Moment(object.date_created).isAfter(Moment().startOf('year'))):
        objectDivider = 'year';
        textDisplay = 'This Year';
        break;
      default:
        objectDivider = 'forever';
        textDisplay = 'Older';
        break;
    }

    return {
      objectDivider: objectDivider,
      textDisplay: textDisplay
    };
  }

  getAssigneeDividers(object)
  {
    let assignee = false;
    if (object.agents.length > 0) {
      assignee = this.agents[object.agents[0]].name;
    } else if (object.teams.length > 0) {
      assignee = this.teams[object.teams[0]].name;
    } else if (object.departments.length > 0) {
      assignee = this.departments[object.departments[0]].title;
    }

    if (assignee === false) {
      return {
        objectDivider: 'none',
        textDisplay: 'Unassigned'
      }
    }

    return {
      objectDivider: assignee,
      textDisplay: assignee
    }
  }

  getProjectDividers(object)
  {
    let project = false;
    if (object.project) {
      project = this.projects[object.project].title;
    }

    if (project === false) {
      return {
        objectDivider: 'none',
        textDisplay: 'No Project'
      }
    }

    return {
      objectDivider: project,
      textDisplay: project
    }
  }

  render() {
    const {taskFrameList} = this.props;
    const _this = this;
    let linked_items = {};

    // Attach IDs to the projects
    if (taskFrameList.taskFrameProjects && typeof taskFrameList.taskFrameProjects.forEach === 'function') {
      taskFrameList.taskFrameProjects.forEach((project) => {
        this.projects[project.id.toString()] = project;
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
        this.agents[agent.id.toString()] = agent;
      });
    }
    if (taskFrameList.taskFrameTeams && typeof taskFrameList.taskFrameTeams.forEach === 'function') {
      taskFrameList.taskFrameTeams.forEach((team) => {
        this.teams[team.id.toString()] = team;
      });
    }
    if (taskFrameList.taskFrameDepartments && typeof taskFrameList.taskFrameDepartments.forEach === 'function') {
      taskFrameList.taskFrameDepartments.forEach((department) => {
        this.departments[department.id.toString()] = department;
      });
    }

    return (
      <section className="task-list-frame dp-list-frame">
      <div className="ticket-list">

        <div className="tickets-control-bar">

          <div className="bulk-edit-control">
            <a href="#" onClick={this.toggleAllMassActions.bind(this)}>
              <span className="checkbox">{this.state.actionable.length > 0 ? <i className="fa fa-check" /> : ''}</span>
            </a>
            <span className="count" style={this.state.actionable.length > 0 ? {} : {display: "none"}}><span>{this.state.actionable.length}</span></span>
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

            <a href="#" className="cancel" onClick={this.hideMassActionControls.bind(this)}>
              <span>Cancel</span>
            </a>
          </span>
        </div>

        <Formsy.Form onSubmit={_this.createTask.bind(_this, taskFrameList.taskFrameSource)}>
          <FRC.Input name="title" type="text" />
          <button type="submit" value="Save" className="button">Add</button>
        </Formsy.Form>

        {taskFrameList.taskFrameList ? taskFrameList.taskFrameList.map((object) => {

          // Temporary hack
          let tempOrder = "created";
          let divider = this.dividerRequired(object, tempOrder);

          return <span>
            {
              divider ? <div className="divider"><hr/><h1><span>{divider}</span></h1></div> : ''
            }
            <TaskCard task={object} projects={this.projects} linked_items={linked_items} departments={this.departments}
                           teams={this.teams} agents={this.agents} toggleDone={this.toggleDone.bind(this)} key={object.id}
                           source={taskFrameList.taskFrameSource} dispatch={_this.props.dispatch.bind(_this)}
                           editTask={_this.editTask.bind(_this)} updateMassActions={_this.updateMassActions.bind(_this)}
                           selected={_this.state.actionable.indexOf(object.id) !== -1} />
          </span>
        }) : '' }
      </div>
    </section>
    );
  }
}
