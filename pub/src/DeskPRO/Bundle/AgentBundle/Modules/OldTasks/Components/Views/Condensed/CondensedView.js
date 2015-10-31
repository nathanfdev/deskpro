import React from 'react';
import Formsy from 'formsy-react';
import FRC from 'DeskPRO/Component/FormComponents/main.js';
import TaskCardCondensedGroup from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/Components/TaskCardCondensedGroup';
import * as TaskActions from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/Actions/TaskListActions';

export default class CondensedView extends React.Component {
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
    toggleOrder: React.PropTypes.func,
    updateMassActions: React.PropTypes.func
  }

  createTask(source, model) {
    this.props.dispatch(TaskActions.createTask({
      title: model.title
    }, source));
  }

  render() {
    // Missing lists
    const source = this.props.source || '';
    const _this = this;

    return (<div>
      <table cellSpacing="0" className="condensed-task-list">
        <thead>
          <tr>
            <th>Title</th>
            <th className="clickable-column" onClick={this.props.toggleOrder.bind(this, 'project')}>
              Project {this.props.order === 'project' ?
                    <span>{this.props.direction === 'desc' ? <i className="fa fa-caret-down"/> : <i
                                      className="fa fa-caret-up"/>}</span>
              : ''}</th>
            <th className="clickable-column" onClick={this.props.toggleOrder.bind(this, 'due')}>
              Due {this.props.order === 'due' ?
                    <span>{this.props.direction === 'desc' ? <i className="fa fa-caret-down"/> : <i
                                      className="fa fa-caret-up"/>}</span>
              : ''}</th>
            <th className="clickable-column" onClick={this.props.toggleOrder.bind(this, 'assignee')}>
              Assignee {this.props.order === 'assignee' ?
                    <span>{this.props.direction === 'desc' ? <i className="fa fa-caret-down"/> : <i
                                      className="fa fa-caret-up"/>}</span>
              : ''}</th>
          </tr>
        </thead>
        {this.props.rawGroupings ? this.props.rawGroupings.map((group) => {
          if (this.props.groupedTasks[group.key]) {
            return (<TaskCardCondensedGroup tasks={this.props.groupedTasks[group.key]} key={group.id}
                                            source={source}
                                            dispatch={_this.props.dispatch.bind(_this)}
                                            updateField={group.updateField}
                                            updateValue={group.updateValue}
                                            teams={this.props.teams} projects={this.props.projects}
                                            departments={this.props.departments}
                                            agents={this.props.agents} tickets={this.props.tickets}
                                            toggleDone={this.props.toggleDone}
                                            editTask={this.props.editTask}
                                            updateMassActions={this.props.updateMassActions}
                                            actionable={this.props.actionable}
                                            order={this.props.order}
                                            divider={group.title}
                                            moveCard={this.props.moveCard}/>);
          }
        }) : '' }
      </table>
      <Formsy.Form onSubmit={this.createTask.bind(this, source)}>
        <FRC.Input name="title" type="text"/>
        <button type="submit" value="Save" className="button">Add</button>
      </Formsy.Form>
    </div>);
  }
}
