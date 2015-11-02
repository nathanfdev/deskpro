import React from 'react';
import KanbanColumn from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/Components/KanbanColumn';
import * as TaskActions from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/Actions/TaskListActions';

export default class KanbanView extends React.Component {
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
    rawGroupings: React.PropTypes.array,
    source: React.PropTypes.string,
    teams: React.PropTypes.object,
    tickets: React.PropTypes.object,
    ticketsStatus: React.PropTypes.object,
    toggleAssignWindow: React.PropTypes.func,
    toggleDone: React.PropTypes.func,
    updateMassActions: React.PropTypes.func
  }

  createTask(source, model) {
    this.props.dispatch(TaskActions.createTask({
      title: model.title
    }, source));
  }

  render() {
    const source = this.props.source || '';

    const _this = this;

    return (<div className="kanban-columns">
      {this.props.rawGroupings ? this.props.rawGroupings.map((group) => {
        return (<KanbanColumn projects={this.props.projects} agents={this.props.agents} teams={this.props.teams}
                              departments={this.props.departments}
                              tasks={this.props.groupedTasks[group.key]} key={group.id} taskList={group}
                              dispatch={_this.props.dispatch.bind(_this)}
                              updateField={group.updateField}
                              updateValue={group.updateValue}
                              source={source}
                              updateMassActions={this.props.updateMassActions}
                              moveCard={this.props.moveCard}
                              actionable={this.props.actionable}
                              order={this.props.order}
                              editTask={this.props.editTask} />);
      }) : '' }
    </div>);
  }
}
