import React, { PropTypes } from 'react';
import classNames from 'classnames';
import moment from 'moment';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { Calendar } from './Calendar';

export class DueDate extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
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
    const { value, onChange } = this.props;
    const dateFormatted = value && moment(value).format('hh:mm a');
    const isOverdue = value && moment(value).isBefore();

    return (
      <div className="dpwd--card-line-item">
        <span className={classNames({'overdue': isOverdue})} onClick={this.onOpenCalendar}>
          <i className="fa fa-calendar-o"/> Due: {dateFormatted || 'N/A'}
        </span>

        <Detached isOpen={this.state.calendarOpened}
                  positionTarget={this}
                  positionAt="right+5 top-10">

          <ClickOut onClickOut={this.onCloseCalendar}>
            <Calendar value={value}
                      onChange={onChange} />
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
