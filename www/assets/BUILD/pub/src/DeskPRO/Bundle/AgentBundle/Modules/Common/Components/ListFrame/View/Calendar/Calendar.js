import React from 'react';
import { Controls } from './ControlBar/Controls';
import { CalendarHeader } from './CalendarHeader';
import { CalendarBody } from './CalendarBody';
import moment from 'moment';

export class Calendar extends React.Component {

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
        <Controls date={this.state.date} onChange={this.onChangeDate} />
        <table className="calendar-content">
          <CalendarHeader date={this.state.date} />
          <CalendarBody date={this.state.date} {...this.props} />
        </table>
      </div>
    );
  }
}
