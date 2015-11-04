import React, { PropTypes } from 'react';
import classNames from 'classnames';
import moment from 'moment';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { Calendar } from './Calendar';

export class DueDate extends React.Component {

  static propTypes = {
    date: PropTypes.string
  };

  constructor(props) {
    super(props);

    this.state = {
      calendarOpened: false
    };
  }

  onOpenCalendar = () => {
    this.setState({
      calendarOpened: true
    });
  };

  onCloseCalendar = () => {
    this.setState({
      calendarOpened: false
    });
  };

  render() {
    const { date } = this.props;
    const dateFormatted = date && moment(date).format('hh:mm a');
    const isOverdue = date && moment(date).isBefore();

    return (
      <div className="dpwd--card-line-item">
        <span className={classNames({'overdue': isOverdue})} onClick={this.onOpenCalendar}>
          <i className="fa fa-calendar-o"/> Due: {dateFormatted || 'N/A'}
        </span>

        <Detached isOpen={this.state.calendarOpened}
                  positionTarget={this}
                  positionAt="right+5 top-10">

          <ClickOut onClickOut={this.onCloseCalendar}>
            <Calendar />
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
