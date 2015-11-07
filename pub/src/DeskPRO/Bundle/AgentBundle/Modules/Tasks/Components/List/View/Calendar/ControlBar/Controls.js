import React from 'react';
import { Year } from './Year';
import { Month } from './Month';
import moment from 'moment';

export class Controls extends React.Component {

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

    console.log(date);
  };

  render() {
    return (
      <div className="dpwd-calendar-controls">
        <Year date={this.state.date}
              onChange={this.onChangeDate} />
        <Month date={this.state.date}
               onChange={this.onChangeDate} />
      </div>
    );
  }
}
