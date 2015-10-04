import React from "react";
import { DragSource } from "react-dnd";
import { connect } from 'react-redux';
import $ from 'jquery';
import * as TaskActions from "../Actions/TaskListActions";
import { IntlMixin, FormattedDate } from "react-intl";
import Formsy from "formsy-react";
import FRC from "../../../../../Component/FormComponents/main.js";
import TaskCard from "../Components/TaskCard";
import TaskControls from "../Components/TaskControls";
import TaskCardGroup from "../Components/TaskCardGroup";
import TaskCalendar from "../Components/TaskCalendar";
import TaskCardCondensedGroup from "../Components/TaskCardCondensedGroup";
import TaskOrderHover from "../Components/TaskOrderHover";
import KanbanColumn from "../Components/KanbanColumn";
import Moment from "moment";
import TaskGrouping from "../../../Services/TaskGrouping";
import * as AppActions from "../../Application/Actions/AppActions";
import * as constants from "../../../Constants/Constants";
import ReactPaginate from "../../Application/Components/Pagination/deskpro-react-paginate";
import ComponentRootWrapper from "DeskPRO/Component/ComponentRootWrapper";
import AssignHover from "../Components/AssignHover";
import TaskControlsViewSwitcher from '../Components/TaskControlsViewSwitcher';

import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import ItemGroup from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemGroup';
import ItemList from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemList';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';
import MenuFooterLink from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterLink';
import TaskMassActions from '../Components/TaskMassActions';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';

@connect(state => ({
  taskFrameList: state.taskFrameList,
  taskListList: state.taskListList,
  projectList: state.projectList,
  taskFilter: state.taskFilter,
  labelList: state.labelList,
  agentList: state.agentList,
  teamList: state.teamList,
  departmentList: state.departmentList,
  dp_window: state.Application.dp_window
}))
export default
class TasksListFrame extends React.Component {
  constructor(props) {
    super(props);

    this.state        = {
      actionable: [],
      view: constants.VIEW_MODE_LIST,
      changeView: false,
      order: 'due',
      direction: constants.ORDER_ASC,
      filter: {},
      moment: new Moment(),
      showAssignWindow: false,
      position: {},
      taskData: {},
      massActionable: {}
    };
    this.intl         = IntlMixin;
    this.lastGrouping = '';
    this.agents       = [];
    this.teams        = [];
    this.departments  = [];
    this.projects     = [];

    // Temp project ID
    props.dispatch(TaskActions.loadLists(1));
  }

  toggleDone(object, reload) {
    object.is_done = !object.is_done;

    let newValues = {
      taskId: object.id,
      is_done: object.is_done
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
      title: model.title
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

  viewSwitcherPosition() {
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
    let actionable        = this.state.actionable;
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
      this.hideMassActionControls();
    }
  }

  showMassActionControls() {
    $('.ticket-controls-bulk-editing').animate({'left': '22px'});
  }

  hideMassActionControls() {
    this.setState({
      actionable: []
    });
    $('.ticket-controls-bulk-editing').animate({'left': '100%'});
  }

  toggleView() {
    this.setState({
      changeView: !this.state.changeView
    });
  }

  applyFilter(filter) {
    this.setState({
      filter: filter
    });

    let query = filter;

    query.order_by = this.state.order;
    query.sort     = this.state.direction;

    this.props.dispatch(TaskActions.setFilter(query));
  }

  setView(view) {
    this.props.dispatch(AppActions.toggleView(view));

    this.setState({
      view: view,
      changeView: false
    });

    // force a reload so we get the correct data
    this.props.dispatch(TaskActions.setFilter(this.state.filter));
  }

  toggleOrder(field) {
    let direction = 'asc';

    if (field === this.state.order) {
      direction = this.state.direction === 'asc' ? 'desc' : 'asc';
    }

    const model = {
      order: field,
      direction: direction
    };

    this.setSortOrder(model);
  }

  setSortOrder(modifier) {
    const query = this.state.filter;

    if (modifier.order) {
      query.order_by = modifier.order;
    }

    if (modifier.direction) {
      query.sort = modifier.direction;
    }

    this.setState(modifier);

    this.props.dispatch(TaskActions.setFilter(query));
  }

  handlePageClick(data) {
    const page = data.selected + 1;

    let filter = this.state.filter;

    filter.page = page;

    this.props.dispatch(TaskActions.setFilter(filter));
  }

  nextMonth() {
    let moment = this.state.moment.add(1, 'months');

    this.setState({
      moment: moment
    });
  }

  prevMonth() {
    let moment = this.state.moment.subtract(1, 'months');

    this.setState({
      moment: moment
    });
  }

  setYear(year) {
    let moment = this.state.moment;
    moment.year(year);

    this.setState({
      moment: moment
    });
  }

  massEdit(data) {
    this.props.dispatch(TaskActions.massEditTasks(
      data,
      this.props.taskFrameList.taskFrameSource
    ));
  }

  handleAssigneeChange(assignee) {
    let task         = {};
    const assignment = assignee.value;

    task.agents      = [];
    task.teams       = [];
    task.departments = [];

    if (assignment !== 'unassigned') {
      let assignmentParts      = assignment.split('-');
      task[assignmentParts[0]] = [assignmentParts[1]];
    }

    if (typeof assignee.id === 'number') {
      task.taskId = assignee.id;
      this.closeAssignWindow();

      this.editTask(this.props.taskFrameList.taskFrameSource, task);
    }
  }

  toggleAssignWindow(task = {}) {
    let target   = $(event.target).closest('div.top-right-box');
    let modifier = 12;

    if (typeof target[0] === 'undefined') {
      target   = $(event.target).closest('.list-sidebar-title');
      modifier = 13;
    }

    this.setState({
      showAssignWindow: !this.state.showAssignWindow,
      position: {
        x: target[0].getBoundingClientRect().right,
        y: target[0].getBoundingClientRect().top + modifier
      },
      taskData: this.state.showAssignWindow ? {} : task
    });
  }

  moveCard(item, targetItem, tasks, callback) {
    const cards   = tasks;
    const id      = item.id;
    const afterId = targetItem.id;

    let oldOrder = [];
    tasks.forEach((card) => {
      oldOrder.push(card.display_order);
    });

    const card       = cards.filter(c => c.id === id)[0];
    const afterCard  = cards.filter(c => c.id === afterId)[0];
    const cardIndex  = cards.indexOf(card);
    const afterIndex = cards.indexOf(afterCard);

    cards.splice(cardIndex, 1);
    cards.splice(afterIndex, 0, card);

    // Used to set the state of the column
    callback(cards);

    this.editTask(
      this.props.taskFrameList.taskFrameSource,
      {
        taskId: card.id,
        display_order: targetItem.display_order
      }
    );
  }

  closeAssignWindow() {
    this.setState({
      taskData: {},
      showAssignWindow: false
    });
  }

  render() {
    const {taskFrameList, projectList, taskFilter, labelList, agentList, teamList, departmentList} = this.props;

    const _this     = this;
    let linkedItems = {};
    let lists       = [];
    let labels      = [];
    let tickets     = {};

    // Attach IDs to the projects
    if (projectList.projectList && typeof projectList.projectList.forEach === 'function') {
      projectList.projectList.forEach((project) => {
        this.projects[project.id.toString()] = project;
      });
    }

    // Attach IDs to the linked item
    if (taskFrameList.taskFrameLinks && typeof taskFrameList.taskFrameLinks.forEach === 'function') {
      taskFrameList.taskFrameLinks.forEach((link) => {
        linkedItems[link.id.toString()] = link;
      });
    }

    // Attach assignments
    if (agentList.agentList && typeof agentList.agentList.forEach === 'function') {
      agentList.agentList.forEach((agent) => {
        this.agents[agent.id.toString()] = agent;
      });
    }
    if (teamList.teamList && typeof teamList.teamList.forEach === 'function') {
      teamList.teamList.forEach((team) => {
        this.teams[team.id.toString()] = team;
      });
    }
    if (departmentList.departmentList && typeof departmentList.departmentList.forEach === 'function') {
      departmentList.departmentList.forEach((department) => {
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

    if (labelList.labelList && typeof labelList.labelCharacters.forEach === 'function') {
      labelList.labelCharacters.forEach((character) => {
        labelList.labelList[character].forEach((label) => {
          labels[label.id.toString()] = label;
        });
      });
    }

    const grouping     = new TaskGrouping(this.projects, this.departments, this.teams, this.agents, lists, linkedItems, tickets);
    const columnField  = this.state.order;
    const rawGroupings = grouping.getRawGroupings(columnField, this.state.direction);
    const sectionClass = this.state.view !== 'list' ? "task-list-frame dp-list-frame kanban" : "task-list-frame dp-list-frame";

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

    let totalPages = 1;

    if (taskFrameList.taskFrameMeta && taskFrameList.taskFrameMeta.pagination) {
      totalPages = taskFrameList.taskFrameMeta.pagination.total_pages;
    }


    return (
      <section className={sectionClass}>
        <div className="ticket-list">
          <ComponentRootWrapper open={this.state.showAssignWindow}>
            <AssignHover position={this.state.position}
                         assignTask={this.handleAssigneeChange.bind(this)}
                         agents={this.agents}
                         teams={this.teams}
                         departments={this.departments}
                         taskData={this.state.taskData}
                         closeWindow={this.closeAssignWindow.bind(this)}/>
          </ComponentRootWrapper>
          <TaskControls toggleView={this.toggleView.bind(this)}
                        agents={this.agents}
                        teams={this.teams}
                        departments={this.departments}
                        projects={this.projects}
                        labels={labels}
                        applyFilter={this.applyFilter.bind(this)}
                        taskFilter={taskFilter}
                        windowProps={this.props.dp_window}
                        order={this.state.order}
                        direction={this.state.direction}
                        setSortOrder={this.setSortOrder.bind(this)}
                        setView={this.setView.bind(this)}
                        actionable={this.state.actionable.length}
                        toggleAllMassActions={this.toggleAllMassActions.bind(this)}
            />

          <TaskMassActions hideMassActionControls={this.hideMassActionControls.bind(this)}
                           projects={this.projects}/>


          <ListFrameContents>
            {this.state.view === 'kanban' ?
             <div className="kanban-columns">
               {rawGroupings ? rawGroupings.map((group) => {
                 return (<KanbanColumn projects={this.projects} agents={this.agents} teams={this.teams}
                                       departments={this.departments}
                                       tasks={tasks[group.key]} key={group.id} taskList={group}
                                       dispatch={_this.props.dispatch.bind(_this)}
                                       columnField={columnField}
                                       updateField={group.updateField}
                                       updateValue={group.updateValue}
                                       source={taskFrameList.taskFrameSource}
                                       updateMassActions={_this.updateMassActions.bind(_this)}
                                       moveCard={this.moveCard.bind(this)}
                                       massEdit={this.massEdit.bind(this)}
                                       actionable={_this.state.actionable}
                                       order={this.state.order}
                                       editTask={_this.editTask.bind(_this)}/>);
               }) : '' }
             </div>
              : (this.state.view === 'condensed') ?
                <div>
                  <table cellSpacing="0" className="condensed-task-list">
                    <thead>
                    <tr>
                      <th>Title</th>
                      <th className="clickable-column" onClick={this.toggleOrder.bind(this, 'project')}>
                        Project {this.state.order === 'project' ?
                                 this.state.direction === 'desc' ? <i className="fa fa-caret-down"/> : <i
                                   className="fa fa-caret-up"/>
                        : ''}</th>
                      <th className="clickable-column" onClick={this.toggleOrder.bind(this, 'due')}>
                        Due {this.state.order === 'due' ?
                             this.state.direction === 'desc' ? <i className="fa fa-caret-down"/> : <i
                               className="fa fa-caret-up"/>
                        : ''}</th>
                      <th className="clickable-column" onClick={this.toggleOrder.bind(this, 'assignee')}>
                        Assignee {this.state.order === 'assignee' ?
                                  this.state.direction === 'desc' ? <i className="fa fa-caret-down"/> : <i
                                    className="fa fa-caret-up"/>
                        : ''}</th>
                    </tr>
                    </thead>
                    {rawGroupings ? rawGroupings.map((group) => {
                      if (tasks[group.key]) {
                        return (<TaskCardCondensedGroup tasks={tasks[group.key]} key={group.id}
                                                        columnField={columnField}
                                                        source={taskFrameList.taskFrameSource}
                                                        dispatch={_this.props.dispatch.bind(_this)}
                                                        updateField={group.updateField}
                                                        updateValue={group.updateValue}
                                                        teams={this.teams} projects={this.projects}
                                                        linked_items={linkedItems}
                                                        departments={this.departments}
                                                        agents={this.agents} tickets={tickets}
                                                        toggleDone={this.toggleDone.bind(this)}
                                                        editTask={_this.editTask.bind(_this)}
                                                        updateMassActions={_this.updateMassActions.bind(_this)}
                                                        actionable={_this.state.actionable}
                                                        order={this.state.order}
                                                        divider={group.title}
                                                        moveCard={this.moveCard.bind(this)}/>);
                      }
                    }) : '' }
                  </table>
                  <Formsy.Form onSubmit={_this.createTask.bind(_this, taskFrameList.taskFrameSource)}>
                    <FRC.Input name="title" type="text"/>
                    <button type="submit" value="Save" className="button">Add</button>
                  </Formsy.Form>
                </div>
               : (this.state.view === 'calendar') ?
                 <div>
                   <TaskCalendar tasks={taskFrameList.taskFrameList}
                                 moment={this.state.moment}
                                 nextMonth={this.nextMonth.bind(this)}
                                 prevMonth={this.prevMonth.bind(this)}
                                 dispatch={_this.props.dispatch.bind(_this)}
                                 tickets={tickets}
                                 teams={this.teams}
                                 projects={this.projects}
                                 linked_items={linkedItems}
                                 departments={this.departments}
                                 agents={this.agents}
                                 setYear={this.setYear.bind(this)}/>
                 </div>
                  :
                 <div>
                   <Formsy.Form onSubmit={_this.createTask.bind(_this, taskFrameList.taskFrameSource)}>
                     <FRC.Input name="title" type="text"/>
                     <button type="submit" value="Save" className="button">Add</button>
                   </Formsy.Form>

                   {rawGroupings ? rawGroupings.map((group) => {
                     return (<TaskCardGroup tasks={tasks[group.key]} key={group.id}
                                            columnField={columnField}
                                            source={taskFrameList.taskFrameSource}
                                            dispatch={_this.props.dispatch.bind(_this)}
                                            updateField={group.updateField}
                                            updateValue={group.updateValue}
                                            teams={this.teams} projects={this.projects}
                                            linked_items={linkedItems}
                                            departments={this.departments} agents={this.agents}
                                            tickets={tickets}
                                            toggleDone={this.toggleDone.bind(this)}
                                            editTask={_this.editTask.bind(_this)}
                                            updateMassActions={_this.updateMassActions.bind(_this)}
                                            actionable={_this.state.actionable}
                                            divider={group.title}
                                            order={this.state.order}
                                            toggleAssignWindow={_this.toggleAssignWindow.bind(_this)}
                                            moveCard={this.moveCard.bind(this)}/>);
                   }) : '' }

                 </div>
            }
          </ListFrameContents>
        </div>

        {
          // pageNum: The total number of pages
          // pageRangeDisplayed: Number of pages to display in the center
          // marginPagesDisplayed: Number of pages to display at the ends of the pagination block
          // initialSelected: Current page INDEX (zero-based - i.e. page number minus 1)
        }

        { totalPages > 1 && this.state.view !== 'calendar' ?
          <div className="dpw--ticket-pagination">
            <ReactPaginate previousLabel={<i className="fa fa-caret-left" />}
                           nextLabel={<i className="fa fa-caret-right" />}
                           breakLabel={<li className="break"><a href="#">...</a></li>}
                           pageNum={totalPages}
                           marginPagesDisplayed={3}
                           pageRangeDisplayed={3}
                           clickCallback={this.handlePageClick.bind(this)}
                           containerClassName={"pages-list"}
                           subContainerClassName={"pages-list sublist"}
                           activeClassName={"active"}/>
          </div> : '' }
      </section>
    );
  }
}
