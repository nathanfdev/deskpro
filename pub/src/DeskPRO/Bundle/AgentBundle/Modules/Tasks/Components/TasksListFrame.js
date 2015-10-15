import React from 'react';
import { connect } from 'react-redux';
import jQuery from 'jquery';
import * as TaskActions from '../Actions/TaskListActions';
import { IntlMixin } from 'react-intl';
import Formsy from 'formsy-react';
import FRC from '../../../../../Component/FormComponents/main.js';
import TaskControls from '../Components/TaskControls';
import TaskCardGroup from '../Components/TaskCardGroup';
import TaskCalendar from '../Components/TaskCalendar';
import TaskCardCondensedGroup from '../Components/TaskCardCondensedGroup';
import KanbanColumn from '../Components/KanbanColumn';
import Moment from 'moment';
import TaskGrouping from '../../../Services/TaskGrouping';
import * as AppActions from '../../Application/Actions/AppActions';
import * as constants from '../../../Constants/Constants';
import ReactPaginate from '../../Common/Components/Pagination/deskpro-react-paginate';
import ComponentRootWrapper from 'DeskPRO/Component/ComponentRootWrapper';
import AssignHover from '../Components/AssignHover';

import TaskMassActions from '../Components/TaskMassActions';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

import { loadLinkedItems } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/RecordStores/Actions/linkedItemActions';

import { filteredTasksSelector, statusFilteredTasksSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/RecordStores/Selectors/taskSelectors';
import { allProjectsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/RecordStores/Selectors/projectSelectors';
import { createLinkedItemRequestSelectors } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/RecordStores/Selectors/linkedItemSelectors';
import { createTicketRequestSelectors } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/RecordStores/Selectors/ticketSelectors';
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { agentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { allDepartmentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';


const requestId = 'taskListFrame';
const ticketsSelector = createTicketRequestSelectors(requestId);
const linkedItemsSelector = createLinkedItemRequestSelectors(requestId);

@connect(state => ({
  taskFrameList: state.Tasks.taskFrameList,
  taskListList: state.Tasks.taskListList,
  projectList: state.Tasks.projectList,
  taskFilter: state.Tasks.taskFilter,
  labelList: state.Tasks.labelList,
  agentList: state.Tasks.agentList,
  teamList: state.Tasks.teamList,
  departmentList: state.Tasks.departmentList,
  dpWindow: state.Application.dpWindow,
  tasks: filteredTasksSelector(state),
  status: statusFilteredTasksSelector(state),
  projects: allProjectsSelector(state),
  agents: agentsSelector(state),
  agentTeams: agentTeamsSelector(state),
  departments: allDepartmentsSelector(state),
  tickets: ticketsSelector.recordsSel(state),
  linkedItems: linkedItemsSelector.recordsSel(state)
}))

export class TasksListFrame extends React.Component {
  static propTypes = {
    dispatch: React.PropTypes.func,
    taskFrameList: React.PropTypes.object,
    taskListList: React.PropTypes.object,
    projectList: React.PropTypes.object,
    taskFilter: React.PropTypes.object,
    labelList: React.PropTypes.object,
    agentList: React.PropTypes.object,
    teamList: React.PropTypes.object,
    departmentList: React.PropTypes.object,
    dpWindow: React.PropTypes.object,
    tasks: React.PropTypes.object,
    status: React.PropTypes.object,
    projects: React.PropTypes.object,
    agents: React.PropTypes.object,
    agentTeams: React.PropTypes.object,
    departments: React.PropTypes.object,
    tickets: React.PropTypes.object,
    linkedItems: React.PropTypes.object
  }

  constructor(props) {
    super(props);

    this.state = {
      actionable: [],
      view: constants.VIEW_MODE_CARD,
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
    this.intl = IntlMixin;
    this.lastGrouping = '';
    this.agents = [];
    this.teams = [];
    this.departments = [];
    this.projects = [];

    // Temp project ID
    props.dispatch(TaskActions.loadLists(1));

    // Demo get linked items
    if (props.tasks.size > 0) {
      const linkedItemIds = [];
      const filteredLinkedItemIds = [];
      props.tasks.map(task => {
        if (task.get('linked_items')) {
          linkedItemIds.concat('linked_items');
        }
      });

      if (linkedItemIds.length > 0) {
        linkedItemIds.map((itemId) => {
          if (filteredLinkedItemIds.indexOf(itemId) < 0) {
            filteredLinkedItemIds.push(itemId);
          }
        });
      }

      if (linkedItemIds.length > 0) {
        props.dispatch(loadLinkedItems(requestId, filteredLinkedItemIds));
      }
    }
  }

  setYear(year) {
    const moment = this.state.moment;
    moment.year(year);

    this.setState({
      moment: moment
    });
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

  setView(view) {
    this.props.dispatch(AppActions.toggleView(view));

    this.setState({
      view: view,
      changeView: false
    });

    // force a reload so we get the correct data
    this.props.dispatch(TaskActions.setFilter(this.state.filter));
  }

  applyFilter(filter) {
    this.setState({
      filter: filter
    });

    const query = filter;

    query.order_by = this.state.order;
    query.sort = this.state.direction;

    this.props.dispatch(TaskActions.setFilter(query));
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

  handlePageClick(data) {
    const page = data.selected + 1;

    const filter = this.state.filter;

    filter.page = page;

    this.props.dispatch(TaskActions.setFilter(filter));
  }

  nextMonth() {
    const moment = this.state.moment.add(1, 'months');

    this.setState({
      moment: moment
    });
  }

  prevMonth() {
    const moment = this.state.moment.subtract(1, 'months');

    this.setState({
      moment: moment
    });
  }

  toggleView() {
    this.setState({
      changeView: !this.state.changeView
    });
  }

  toggleAllMassActions() {
    const taskFrameList = this.props.taskFrameList ? this.props.taskFrameList.get('taskFrameList', []) : [];

    if (this.state.actionable.length === taskFrameList.length) {
      this.hideMassActionControls();
    } else {
      const actionable = [];

      taskFrameList.map((object) => {
        actionable.push(object.id);
      });

      this.setState({
        actionable: actionable
      });
      this.showMassActionControls();
    }
  }

  viewSwitcherPosition() {
    const taskFrameList = this.props.taskFrameList ? this.props.taskFrameList.get('taskFrameList', []) : [];

    if (this.state.actionable.length === taskFrameList.length) {
      this.hideMassActionControls();
    } else {
      const actionable = [];

      taskFrameList.map((object) => {
        actionable.push(object.id);
      });

      this.setState({
        actionable: actionable
      });
      this.showMassActionControls();
    }
  }

  updateMassActions(taskId) {
    const actionable = this.state.actionable;
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

  toggleDone(object, reload) {
    const newValues = {
      taskId: object.get('id'),
      is_done: !object.get('is_done')
    };

    object.set('is_done', newValues.is_done);

    if (newValues.is_done === true) {
      newValues.percent_complete = 100;
    }

    this.forceUpdate();

    this.props.dispatch(TaskActions.editTask(newValues, reload));
  }

  showMassActionControls() {
    jQuery('.ticket-controls-bulk-editing').animate({'left': '22px'});
  }

  hideMassActionControls() {
    this.setState({
      actionable: []
    });
    jQuery('.ticket-controls-bulk-editing').animate({'left': '100%'});
  }

  editTask(source, model) {
    this.props.dispatch(TaskActions.editTask(model, source));
  }

  createTask(source, model) {
    this.props.dispatch(TaskActions.createTask({
      title: model.title
    }, source));
  }

  massEdit(data) {
    const source = this.props.taskFrameList ? this.props.taskFrameList.get('taskFrameSource') : null;

    this.props.dispatch(TaskActions.massEditTasks(
      data,
      source
    ));
  }

  handleAssigneeChange(assignee) {
    const task = {};
    const assignment = assignee.value;

    task.agents = [];
    task.teams = [];
    task.departments = [];

    if (assignment !== 'unassigned') {
      const assignmentParts = assignment.split('-');
      task[assignmentParts[0]] = [assignmentParts[1]];
    }

    if (typeof assignee.id === 'number') {
      task.taskId = assignee.id;
      this.closeAssignWindow();

      const source = this.props.taskFrameList ? this.props.taskFrameList.get('taskFrameSource') : null;

      this.editTask(source, task);
    }
  }

  toggleAssignWindow(task, event) {
    let target = jQuery(event.target).closest('div.top-right-box');
    let modifier = 12;

    if (typeof target[0] === 'undefined') {
      target = jQuery(event.target).closest('.list-sidebar-title');
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
    const cards = tasks;
    const id = item.get('id');
    const afterId = targetItem.get('id');

    const oldOrder = {};
    tasks.forEach((card) => {
      oldOrder.push(card.display_order);
    });

    const card = cards.filter(filteredCard => filteredCard.get('id') === id)[0];
    const afterCard = cards.filter(filteredCard => filteredCard.get('id') === afterId)[0];
    const cardIndex = cards.indexOf(card);
    const afterIndex = cards.indexOf(afterCard);

    cards.splice(cardIndex, 1);
    cards.splice(afterIndex, 0, card);

    // Used to set the state of the column
    callback(cards);

    const source = this.props.taskFrameList ? this.props.taskFrameList.get('taskFrameSource', {}) : {};

    this.editTask(
      source,
      {
        taskId: card.get('id'),
        display_order: targetItem.get('display_order')
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
    const {taskFilter} = this.props;

    const _this = this;
    const linkedItems = {};
    const lists = {};
    const labels = [];
    const tickets = {};

    const projects = this.props.projectList ? this.props.projectList.get('projectList', []) : [];
    const source = this.props.taskFrameList ? this.props.taskFrameList.get('taskFrameSource', '') : '';

    // Attach IDs to the projects
    if (projects && typeof projects.forEach === 'function') {
      projects.forEach((project) => {
        this.projects[project.id.toString()] = project;
      });
    }

    const taskFrameLinks = this.props.taskFrameList ? this.props.taskFrameList.get('taskFrameLinks', {}) : {};

    // Attach IDs to the linked item
    if (taskFrameLinks && typeof taskFrameLinks.forEach === 'function') {
      taskFrameLinks.forEach((link) => {
        linkedItems[link.id.toString()] = link;
      });
    }

    const agentList = this.props.agentList ? this.props.agentList.get('agentList', {}) : {};
    const teamList = this.props.teamList ? this.props.teamList.get('teamList', {}) : {};
    const departmentList = this.props.departmentList ? this.props.departmentList.get('departmentList', {}) : {};

    // Attach assignments
    if (agentList && typeof agentList.forEach === 'function') {
      agentList.forEach((agent) => {
        this.agents[agent.id.toString()] = agent;
      });
    }
    if (teamList && typeof teamList.forEach === 'function') {
      teamList.forEach((team) => {
        this.teams[team.id.toString()] = team;
      });
    }
    if (departmentList && typeof departmentList.forEach === 'function') {
      departmentList.forEach((department) => {
        this.departments[department.id.toString()] = department;
      });
    }

    const taskFrameTickets = this.props.taskFrameList ? this.props.taskFrameList.get('taskFrameTickets', []) : [];

    // Attach tickets
    if (taskFrameTickets && typeof taskFrameTickets.forEach === 'function') {
      taskFrameTickets.forEach((ticket) => {
        tickets[ticket.id.toString()] = ticket;
      });
    }

    const taskList = this.props.taskListList ? this.props.taskListList.get('taskList', []) : [];

    if (taskList && typeof taskList.forEach === 'function') {
      taskList.forEach((listObject) => {
        lists[listObject.id.toString()] = listObject;
      });
    }

    const labelList = this.props.labelList ? this.props.labelList.get('labelList', []) : [];
    const labelCharacters = this.props.labelList ? this.props.labelList.get('labelCharacters', []) : [];

    if (labelList && typeof labelCharacters.forEach === 'function') {
      labelCharacters.forEach((character) => {
        labelList[character].forEach((label) => {
          labels[label.id.toString()] = label;
        });
      });
    }

    const grouping = new TaskGrouping(this.projects, this.departments, this.teams, this.agents, lists, linkedItems, tickets);
    const columnField = this.state.order;
    const rawGroupings = grouping.getRawGroupings(columnField, this.state.direction);
    const sectionClass = this.state.view !== 'list' ? 'task-list-frame dp-list-frame kanban' : 'task-list-frame dp-list-frame';

    const tasks = [];

    const taskFrameList = this.props.taskFrameList ? this.props.taskFrameList.get('taskFrameList', []) : [];

    // Split tasks up into the appropriate kanban columns
    if (taskFrameList && taskFrameList.size > 0) {
      taskFrameList.forEach((object) => {
        const columnId = grouping.getGroup(object, columnField);

        if (typeof tasks[columnId] === 'undefined') {
          tasks[columnId] = [];
        }

        tasks[columnId].push(object);
      });
    }

    let totalPages = 1;

    const taskMeta = this.props.taskFrameList ? this.props.taskFrameList.get('taskFrameMeta', []) : [];

    if (taskMeta && taskMeta.pagination) {
      totalPages = taskMeta.pagination.total_pages;
    }

    return (
      <ListFrameContainer className={sectionClass}>
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
                      changeView={this.state.changeView}
                      agents={this.agents}
                      teams={this.teams}
                      departments={this.departments}
                      projects={this.projects}
                      labels={labels}
                      applyFilter={this.applyFilter.bind(this)}
                      taskFilter={taskFilter}
                      windowProps={this.props.dpWindow}
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
                                     source={source}
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
                                                      source={source}
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
                <Formsy.Form onSubmit={_this.createTask.bind(_this, source)}>
                  <FRC.Input name="title" type="text"/>
                  <button type="submit" value="Save" className="button">Add</button>
                </Formsy.Form>
              </div>
             : (this.state.view === 'calendar') ?
               <div>
                 <TaskCalendar tasks={taskFrameList}
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
                 <Formsy.Form onSubmit={_this.createTask.bind(_this, source)}>
                   <FRC.Input name="title" type="text"/>
                   <button type="submit" value="Save" className="button">Add</button>
                 </Formsy.Form>

                 {rawGroupings ? rawGroupings.map((group) => {
                   return (<TaskCardGroup tasks={tasks[group.key]} key={group.id}
                                          columnField={columnField}
                                          source={source}
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
      </ListFrameContainer>
    );
  }
}
