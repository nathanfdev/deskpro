import React from 'react';
import { connect } from 'react-redux';
import jQuery from 'jquery';
import * as TaskActions from '../Actions/TaskListActions';
import { IntlMixin } from 'react-intl';
import TaskControls from '../Components/TaskControls';
import ListView from '../Components/Views/List/ListView';
import KanbanView from '../Components/Views/Kanban/KanbanView';
import CondensedView from '../Components/Views/Condensed/CondensedView';
import CalendarView from '../Components/Views/Calendar/CalendarView';
import Moment from 'moment';
import * as AppActions from '../../Application/Actions/appActions';
import * as constants from '../../../Constants/Constants';
import ReactPaginate from '../../Common/Components/Pagination/deskpro-react-paginate';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import AssignHover from '../Components/AssignHover';
import TaskViewConnector from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/Components/Views/TaskViewConnector';
import Immutable from 'immutable';

import TaskMassActions from '../Components/TaskMassActions';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';

// import { filteredTasksSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/RecordStores/Selectors/taskSelectors';
import { allProjectsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Selectors/projectSelectors';
import { createLinkedItemRequestSelectors } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Selectors/linkedItemSelectors';
import { createTicketRequestSelectors } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Selectors/ticketSelectors';
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { agentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { allDepartmentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';
import { allTaskLabelsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Selectors/taskLabelSelectors';
import { taskListSelector, statusFilteredTasksSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/Selectors/taskSelectors';

const requestId = 'taskListFrame';
const ticketsSelector = createTicketRequestSelectors(requestId);
const linkedItemsSelector = createLinkedItemRequestSelectors(requestId);

@connect(state => ({
  agents: agentsSelector(state),
  agentTeams: agentTeamsSelector(state),
  departments: allDepartmentsSelector(state),
  dpWindow: state.Application.dpWindow,
  labels: allTaskLabelsSelector(state),
  linkedItems: linkedItemsSelector.recordsSel(state),
  linkedItemsStatus: linkedItemsSelector.statusSel(state),
  projects: allProjectsSelector(state),
  status: statusFilteredTasksSelector(state),
  taskFilter: state.OldTasks.taskFilter,
  taskFrameList: state.OldTasks.taskFrameList,
  tasks: taskListSelector(state),
  taskSource: state.OldTasks.taskSource,
  tickets: ticketsSelector.recordsSel(state)
}))

export class TasksListFrame extends React.Component {
  static propTypes = {
    agents: React.PropTypes.object,
    agentTeams: React.PropTypes.object,
    departments: React.PropTypes.object,
    dispatch: React.PropTypes.func,
    dpWindow: React.PropTypes.object,
    labels: React.PropTypes.object,
    linkedItems: React.PropTypes.object,
    projects: React.PropTypes.object,
    status: React.PropTypes.object,
    taskFilter: React.PropTypes.object,
    taskFrameList: React.PropTypes.object,
    tasks: React.PropTypes.object,
    taskSource: React.PropTypes.object,
    tickets: React.PropTypes.object
  }

  constructor(props) {
    super(props);

    this.state = {
      actionable: [],
      changeView: false,
      direction: constants.ORDER_ASC,
      filter: {},
      massActionable: {},
      moment: new Moment(),
      order: 'due',
      position: {},
      showAssignWindow: false,
      taskData: {},
      view: constants.VIEW_MODE_CARD
    };
    this.intl = IntlMixin;
    this.lastGrouping = '';
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

  toggleView() {
    this.setState({
      changeView: !this.state.changeView
    });
  }

  toggleAllMassActions() {
    const tasks = this.props.tasks ? this.props.tasks : {};

    if (!tasks || this.state.actionable.length === tasks.size) {
      this.hideMassActionControls();
    } else {
      const actionable = [];

      tasks.map((object) => {
        actionable.push(object.get('id'));
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
    // Make sure the right data gets through
    const task = {
      taskId: model.get('id'),
      title: model.get('title'),
      is_done: model.get('is_done'),
      project: model.get('project'),
      date_due: model.get('date_due'),
      agents: model.get('agents'),
      teams: model.get('teams'),
      departments: model.get('departments'),
      list: model.get('list'),
    };

    this.props.dispatch(TaskActions.editTask(task, source));
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

    if (typeof assignee.taskId === 'number') {
      task.id = assignee.taskId;
      this.closeAssignWindow();

      const source = this.props.taskSource ? this.props.taskSource.get('taskSource', '') : '';

      this.editTask(source, Immutable.Map(task));
    }
  }

  toggleAssignWindow(task, event) {
    const target = jQuery(event.target).closest('.dpw--avatar-face');

    this.setState({
      showAssignWindow: !this.state.showAssignWindow,
      position: {
        x: target[0].getBoundingClientRect().right + 20,
        y: target[0].getBoundingClientRect().top + 12
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

    const source = this.props.taskSource ? this.props.taskSource.get('taskSource', '') : '';

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

    const sectionClass = this.state.view !== 'list' ? 'task-list-frame dp-list-frame kanban' : 'task-list-frame dp-list-frame';
    const source = this.props.taskSource ? this.props.taskSource.get('taskSource', '') : '';

    // Grab the project ID
    const regex = /[?&]project=([0-9]+)/g;
    const match = regex.exec(source);
    const projectId = source && match ? match[1] : false;

    let totalPages = 1;

    const taskMeta = this.props.taskFrameList ? this.props.taskFrameList.get('taskFrameMeta', []) : [];

    if (taskMeta && taskMeta.pagination) {
      totalPages = taskMeta.pagination.total_pages;
    }

    return (
      <ListFrameContainer className={sectionClass}>
        <Positioned isOpen={this.state.showAssignWindow}>
          <AssignHover position={this.state.position}
                       assignTask={this.handleAssigneeChange.bind(this)}
                       agents={this.props.agents}
                       teams={this.props.agentTeams}
                       departments={this.props.departments}
                       taskData={this.state.taskData}
                       closeWindow={this.closeAssignWindow.bind(this)}/>
        </Positioned>
        <TaskControls toggleView={this.toggleView.bind(this)}
                      changeView={this.state.changeView}
                      agents={this.props.agents}
                      teams={this.props.agentTeams}
                      departments={this.props.departments}
                      projects={this.props.projects}
                      labels={this.props.labels}
                      applyFilter={this.applyFilter.bind(this)}
                      taskFilter={taskFilter}
                      windowProps={this.props.dpWindow}
                      order={this.state.order}
                      direction={this.state.direction}
                      setSortOrder={this.setSortOrder.bind(this)}
                      setView={this.setView.bind(this)}
                      view={this.state.view}
                      actionable={this.state.actionable.length}
                      toggleAllMassActions={this.toggleAllMassActions.bind(this)}
          />

        <div>
          { this.props.tasks && this.props.tasks.size > 0 ?
            <ListFrameContents>
              {this.state.view === 'card' ?
                <TaskViewConnector tasks={this.props.tasks}
                                   order={this.state.order}
                                   direction={this.state.direction}
                                   projectId={projectId}>
                  <ListView view={this.state.view}
                            toggleDone={this.toggleDone.bind(this)}
                            editTask={this.editTask.bind(this)}
                            updateMassActions={this.updateMassActions.bind(this)}
                            actionable={this.state.actionable}
                            toggleAssignWindow={this.toggleAssignWindow.bind(this)}
                            moveCard={this.moveCard.bind(this)}
                            source={source} />
                </TaskViewConnector>
              : '' }

              {this.state.view === 'kanban' ?
                <TaskViewConnector tasks={this.props.tasks}
                                   order={this.state.order}
                                   direction={this.state.direction}
                                   projectId={projectId}>
                  <KanbanView view={this.state.view}
                              toggleDone={this.toggleDone.bind(this)}
                              editTask={this.editTask.bind(this)}
                              updateMassActions={this.updateMassActions.bind(this)}
                              actionable={this.state.actionable}
                              toggleAssignWindow={this.toggleAssignWindow.bind(this)}
                              moveCard={this.moveCard.bind(this)}
                              source={source} />
                </TaskViewConnector>
              : '' }

              {this.state.view === 'condensed' ?
                <TaskViewConnector tasks={this.props.tasks}
                                   order={this.state.order}
                                   direction={this.state.direction}
                                   projectId={projectId}>
                  <CondensedView view={this.state.view}
                            toggleDone={this.toggleDone.bind(this)}
                            editTask={this.editTask.bind(this)}
                            updateMassActions={this.updateMassActions.bind(this)}
                            actionable={this.state.actionable}
                            toggleAssignWindow={this.toggleAssignWindow.bind(this)}
                            toggleOrder={this.toggleOrder.bind(this)}
                            moveCard={this.moveCard.bind(this)}
                            source={source} />
                </TaskViewConnector>
              : '' }

              {this.state.view === 'calendar' ?
                <TaskViewConnector tasks={this.props.tasks}
                                   order={this.state.order}
                                   direction={this.state.direction}
                                   projectId={projectId}>
                  <CalendarView view={this.state.view}
                            toggleDone={this.toggleDone.bind(this)}
                            editTask={this.editTask.bind(this)}
                            updateMassActions={this.updateMassActions.bind(this)}
                            actionable={this.state.actionable}
                            toggleAssignWindow={this.toggleAssignWindow.bind(this)}
                            toggleOrder={this.toggleOrder.bind(this)}
                            moveCard={this.moveCard.bind(this)}
                            source={source} />
                </TaskViewConnector>
              : '' }
            </ListFrameContents>
          : '' }
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
      </ListFrameContainer>
    );
  }
}
