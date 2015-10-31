import React from 'react';
import TaskCalendar from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/Components/TaskCalendar';
import * as TaskActions from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/Actions/TaskListActions';
import Moment from 'moment';

export default class CalendarView extends React.Component {
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
    tasks: React.PropTypes.object,
    teams: React.PropTypes.object,
    tickets: React.PropTypes.object,
    ticketsStatus: React.PropTypes.object,
    toggleAssignWindow: React.PropTypes.func,
    toggleDone: React.PropTypes.func,
    toggleOrder: React.PropTypes.func,
    updateMassActions: React.PropTypes.func
  }

  constructor(props) {
    super(props);

    this.state = {
      moment: new Moment()
    };
  }

  setYear(year) {
    const moment = this.state.moment;
    moment.year(year);

    this.setState({
      moment: moment
    });
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

  createTask(source, model) {
    this.props.dispatch(TaskActions.createTask({
      title: model.title
    }, source));
  }

  render() {
    // Missing lists
    const _this = this;

    return (<div>
      <TaskCalendar tasks={this.props.tasks}
                    moment={this.state.moment}
                    nextMonth={this.nextMonth.bind(this)}
                    prevMonth={this.prevMonth.bind(this)}
                    dispatch={_this.props.dispatch.bind(_this)}
                    tickets={this.props.tickets}
                    teams={this.props.teams}
                    projects={this.props.projects}
                    departments={this.props.departments}
                    agents={this.props.agents}
                    setYear={this.setYear.bind(this)} />
    </div>);
  }
}
