import React from 'react';
import Formsy from 'formsy-react';
import FRC from 'DeskPRO/Component/FormComponents/main.js';
import TaskCardGroup from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/Components/TaskCardGroup';
import Moment from 'moment';
import TaskGrouping from 'DeskPRO/Bundle/AgentBundle/Services/TaskGrouping';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import Immutable from 'immutable';
import * as TaskActions from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/Actions/TaskListActions';

export default class ListView extends React.Component {
  static propTypes = {
    actionable: React.PropTypes.array,
    agents: React.PropTypes.object,
    departments: React.PropTypes.object,
    direction: React.PropTypes.string,
    dispatch: React.PropTypes.func,
    editTask: React.PropTypes.func,
    groupedTasks: React.PropTypes.array,
    moveCard: React.PropTypes.func,
    order: React.PropTypes.string,
    projects: React.PropTypes.object,
    rawGroupings: React.PropTypes.object,
    source: React.PropTypes.string,
    tasks: React.PropTypes.object,
    teams: React.PropTypes.object,
    tickets: React.PropTypes.object,
    ticketsStatus: React.PropTypes.object,
    toggleAssignWindow: React.PropTypes.func,
    toggleDone: React.PropTypes.func,
    updateMassActions: React.PropTypes.func
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
  }

  createTask(source, model) {
    this.props.dispatch(TaskActions.createTask({
      title: model.title
    }, source));
  }

  render() {
    // Missing lists
    const source = this.props.source || '';

    return (<div>
      <Formsy.Form onSubmit={this.createTask.bind(this, source)}>
        <FRC.Input name="title" type="text"/>
        <button type="submit" value="Save" className="button">Add</button>
      </Formsy.Form>

      {this.props.rawGroupings ? this.props.rawGroupings.map((group) => {
        return (<TaskCardGroup tasks={this.props.groupedTasks[group.key]} key={group.id}
                               columnField={this.props.order}
                               source={source}
                               dispatch={this.props.dispatch.bind(this)}
                               updateField={group.updateField}
                               updateValue={group.updateValue}
                               teams={this.props.teams} projects={this.props.projects}
                               departments={this.props.departments} agents={this.props.agents}
                               tickets={this.props.tickets}
                               toggleDone={this.props.toggleDone}
                               editTask={this.props.editTask}
                               updateMassActions={this.props.updateMassActions}
                               actionable={this.props.actionable}
                               divider={group.title}
                               order={this.state.order}
                               toggleAssignWindow={this.props.toggleAssignWindow}
                               moveCard={this.props.moveCard}/>);
      }) : ''}
    </div>);
  }
}
