import React from "react";
import { DragSource } from "react-dnd";
import { connect } from 'redux/react';
import $ from 'jquery';
import * as TaskActions from "../Actions/TaskListActions";
import { IntlMixin, FormattedDate } from "react-intl";
import Formsy from "formsy-react";
import FRC from "../../../../../Component/FormComponents/main.js";
import TaskCard from "../Components/TaskCard";
import TaskControls from "../Components/TaskControls";
import TaskCardGroup from "../Components/TaskCardGroup";
import KanbanColumn from "../Components/KanbanColumn";
import Moment from "moment";
import TaskGrouping from "../../../Services/TaskGrouping";
import * as AppActions from "../../Application/Actions/AppActions";

@connect(state => ({
  taskFrameList: state.taskFrameList,
  taskListList: state.taskListList
}))
export default class TasksListFrame extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      actionable: [],
      kanban: false
    };
    this.intl = IntlMixin;
    this.lastGrouping = '';
    this.agents = [];
    this.teams = [];
    this.departments = [];
    this.projects = [];

    // Temp project ID
    props.dispatch(TaskActions.loadLists(63));
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

  toggleView()
  {
    // @TODO: Make this do more than just toggle between kanban and list
    // Nananana-nananana, nananana-nananana KANBANNNN!
    this.props.dispatch(AppActions.toggleKanban());
    this.setState({
      kanban: !this.state.kanban
    });
  }

  render() {
    const {taskFrameList} = this.props;
    const _this = this;
    let linked_items = {};
    let lists = [];
    let tickets = {};

    // Temp projectId for the sake of development
    const projectId = "63";

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

    // Attach tickets
    if (taskFrameList.taskFrameTickets && typeof taskFrameList.taskFrameTickets.forEach === 'function') {
      taskFrameList.taskFrameTickets.forEach((ticket) => {
        tickets[ticket.id.toString()] = ticket;
      });
    }

    if (this.props.taskListList.taskList && typeof this.props.taskListList.taskList.forEach === 'function') {
      this.props.taskListList.taskList.forEach((listObject) => {
        lists[listObject.id.toString()] = listObject;
      });
    }

    const grouping = new TaskGrouping(this.projects, this.departments, this.teams, this.agents, lists, linked_items, tickets);

    // Temporary hack
    const columnField = "list";

    const rawGroupings = grouping.getRawGroupings(columnField);

    const sectionClass = this.state.kanban ? "task-list-frame dp-list-frame kanban" : "task-list-frame dp-list-frame";

    let tasks = [];

    // Split tasks up into the appropriate kanban columns
    if (taskFrameList && taskFrameList.taskFrameList && taskFrameList.taskFrameList.length > 0) {
      taskFrameList.taskFrameList.forEach((object) => {

        let columnId = grouping.getGroup(object, columnField);

        if (typeof tasks[columnId] === 'undefined') {
          tasks[columnId] = [];
        }

        tasks[columnId].push(object);
      });
    }

    return (
      <section className={sectionClass}>
      <div className="ticket-list">

        <div className="tickets-control-bar">

          <div className="bulk-edit-control">
            <a href="#" onClick={this.toggleAllMassActions.bind(this)}>
              <span className="checkbox">{this.state.actionable.length > 0 ? <i className="fa fa-check" /> : ''}</span>
            </a>
            <span className="count" style={this.state.actionable.length > 0 ? {} : {display: "none"}}>
              <span>{this.state.actionable.length}</span>
            </span>
          </div>

          <TaskControls toggleView={this.toggleView.bind(this)} />

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

        {this.state.kanban ?
          <div className="kanban-columns">
            {rawGroupings ? rawGroupings.map((grouping) => {
              return <KanbanColumn projects={this.projects} agents={this.agents} teams={this.teams} departments={this.departments}
                                  tasks={tasks[grouping.key]} key={grouping.id} taskList={grouping}
                                  dispatch={_this.props.dispatch.bind(_this)} columnField={columnField}
                                  updateField={grouping.updateField} updateValue={grouping.updateValue} />
              }) : '' }
            </div>
          :
          <div>
            <Formsy.Form onSubmit={_this.createTask.bind(_this, taskFrameList.taskFrameSource)}>
              <FRC.Input name="title" type="text" />
              <button type="submit" value="Save" className="button">Add</button>
            </Formsy.Form>

            {rawGroupings ? rawGroupings.map((grouping) => {

              return <TaskCardGroup tasks={tasks[grouping.key]} key={grouping.id} columnField={columnField}
                                    source={taskFrameList.taskFrameSource} dispatch={_this.props.dispatch.bind(_this)}
                                    updateField={grouping.updateField} updateValue={grouping.updateValue}
                                    teams={this.teams} projects={this.projects} linked_items={linked_items}
                                    departments={this.departments} agents={this.agents} tickets={tickets}
                                    toggleDone={this.toggleDone.bind(this)} editTask={_this.editTask.bind(_this)}
                                    updateMassActions={_this.updateMassActions.bind(_this)}
                                    actionable={_this.state.actionable}
                                    divider={grouping.title}/>
              }) : '' }

          </div>
        }
      </div>
    </section>
    );
  }
}
