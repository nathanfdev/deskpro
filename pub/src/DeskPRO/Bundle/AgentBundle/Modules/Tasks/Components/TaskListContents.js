import React from 'react';
import { connect } from 'react-redux';
import Formsy from 'formsy-react';
import FRC from '../../../../../Component/FormComponents/main.js';
import TaskCardGroup from '../Components/TaskCardGroup';
import TaskCalendar from '../Components/TaskCalendar';
import TaskCardCondensedGroup from '../Components/TaskCardCondensedGroup';
import KanbanColumn from '../Components/KanbanColumn';
import Moment from 'moment';
import TaskGrouping from '../../../Services/TaskGrouping';
import * as constants from '../../../Constants/Constants';
import Immutable from 'immutable';
import * as TaskActions from '../Actions/TaskListActions';

import { loadTickets } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/RecordStores/Actions/ticketActions';

import { createTicketRequestSelectors } from 'DeskPRO/Bundle/AgentBundle/Modules/Tickets/RecordStores/Selectors/ticketSelectors';

const requestId = 'taskListFrame';
const ticketsSelector = createTicketRequestSelectors(requestId);

@connect(state => ({
  // tasks: state.Tasks.tasks,
  // taskListList: state.Tasks.taskListList,
  // projectList: state.Tasks.projectList,
  // taskFilter: state.Tasks.taskFilter,
  // labelList: state.Tasks.labelList,
  // agentList: state.Tasks.agentList,
  // teamList: state.Tasks.teamList,
  // departmentList: state.Tasks.departmentList,
  // dpWindow: state.Application.dpWindow,
  // status: statusFilteredTasksSelector(state),
  // projects: allProjectsSelector(state),
  // agents: agentsSelector(state),
  // agentTeams: agentTeamsSelector(state),
  // departments: allDepartmentsSelector(state),
  tickets: ticketsSelector.recordsSel(state),
  ticketsStatus: ticketsSelector.statusSel(state)
}))

export default class TaskListContents extends React.Component {
  static propTypes = {
    agentList: React.PropTypes.object,
    agents: React.PropTypes.object,
    agentTeams: React.PropTypes.object,
    children: React.PropTypes.any,
    departmentList: React.PropTypes.object,
    departments: React.PropTypes.object,
    direction: React.PropTypes.string,
    dispatch: React.PropTypes.func,
    dpWindow: React.PropTypes.object,
    groupedTasks: React.PropTypes.array,
    labelList: React.PropTypes.object,
    order: React.PropTypes.string,
    projectList: React.PropTypes.object,
    projects: React.PropTypes.object,
    status: React.PropTypes.object,
    taskFilter: React.PropTypes.object,
    taskListList: React.PropTypes.object,
    tasks: React.PropTypes.object,
    teamList: React.PropTypes.object,
    tickets: React.PropTypes.object,
    ticketsStatus: React.PropTypes.object,

    toggleDone: React.PropTypes.func,
    editTask: React.PropTypes.func,
    updateMassActions: React.PropTypes.func,
    toggleAssignWindow: React.PropTypes.func,
    moveCard: React.PropTypes.func,
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
      massActionable: {},
      loadedAll: false
    };

    this.agents = [];
    this.teams = [];
    this.departments = [];
    this.projects = [];
  }

  componentDidMount() {
    Promise.all([
      this.loadTaskLinkedTickets()
    ]).then(() => {
      console.log('Done');
      // this.setState({
      //   loadedAll: true
      // });
    });
  }

  loadTaskLinkedTickets() {
    if (this.props.tasks && this.props.tasks.size > 0 && !this.props.ticketsStatus.get('isDone')) {
      const linkedTicketIds = Immutable.List();
      const linkedToMerge = [];

      this.props.tasks.map(task => {
        if (task.has('linked_tickets') && task.get('linked_tickets').size > 0) {
          const newLinkedTicketIds = task.get('linked_tickets').toList();
          linkedToMerge.push(newLinkedTicketIds);
        }
      });

      const linkedToLoad = linkedTicketIds.merge(...linkedToMerge);

      const loadArray = linkedToLoad.toArray();

      if (linkedToLoad.size > 0) {
        return this.props.dispatch(loadTickets(requestId, loadArray));
      }
    }
  }

  createTask(source, model) {
    this.props.dispatch(TaskActions.createTask({
      title: model.title
    }, source));
  }

  render() {
    // Missing lists
    const grouping = new TaskGrouping(this.projects, this.departments, this.teams, this.agents, {}, this.props.linkedItems, this.props.tickets);
    const columnField = this.props.order;
    const rawGroupings = grouping.getRawGroupings(columnField, this.state.direction);
    const source = this.props.source || '';

    // debugger;

    return (<div>
      <Formsy.Form onSubmit={this.createTask.bind(this, source)}>
        <FRC.Input name="title" type="text"/>
        <button type="submit" value="Save" className="button">Add</button>
      </Formsy.Form>

      {rawGroupings ? rawGroupings.map((group) => {
        return (<TaskCardGroup tasks={this.props.groupedTasks[group.key]} key={group.id}
                               columnField={columnField}
                               source={source}
                               dispatch={this.props.dispatch.bind(this)}
                               updateField={group.updateField}
                               updateValue={group.updateValue}
                               teams={this.teams} projects={this.projects}
                               departments={this.departments} agents={this.agents}
                               tickets={this.props.tickets}
                               toggleDone={this.props.toggleDone}
                               editTask={this.props.editTask}
                               updateMassActions={this.props.updateMassActions}
                               actionable={this.state.actionable}
                               divider={group.title}
                               order={this.state.order}
                               toggleAssignWindow={this.props.toggleAssignWindow}
                               moveCard={this.props.moveCard}/>);
      }) : ''}
    </div>);
  }
}
