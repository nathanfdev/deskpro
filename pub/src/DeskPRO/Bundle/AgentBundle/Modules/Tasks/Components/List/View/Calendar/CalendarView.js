import React, { PropTypes } from 'react';
import { Controls } from './ControlBar/Controls';
import { CalendarHeader } from './CalendarHeader';
import { CalendarBody } from './CalendarBody';
import { TaskCard } from './TaskCard/TaskCard';
import moment from 'moment';

export class CalendarView extends React.Component {

  static propTypes = {
    tasks: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      date: moment()
    };
  }

  onChangeDate = date => {
    this.setState({
      date: date
    });
  };

  render() {
    return (
      <div>
        <Controls date={this.state.date}
                  onChange={this.onChangeDate} />

        <table className="calendar-content">
          <CalendarHeader date={this.state.date} />
          <CalendarBody />
        </table>

        {this.props.tasks.map((task, index) => <TaskCard task={task} key={index} />)}
      </div>
    );
  }
}
