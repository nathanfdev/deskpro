import React, { PropTypes } from 'react';
import classNames from 'classnames';
import moment from 'moment';
import { HiddenDateTimePicker } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/DateTime/HiddenDateTimePicker';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { DateString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/DateString';

export class DateDue extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      isOpen: false
    };
  }

  onOpenCalendar = () => {
    this.setState({
      isOpen: true
    });
  };

  onCloseCalendar = () => {
    this.setState({
      isOpen: false
    });
  };

  render() {
    const { value, onChange } = this.props;
    const isOverdue = value && moment(value).isBefore();

    return (
      <div className="dpwd--card-line-item" onDoubleClick={this.onOpenCalendar}>
        <span className={classNames({'overdue': isOverdue})}>
          <i className="fa fa-calendar-o"/>
          <i />

          Due: <DateString value={value} />
        </span>
        <Detached isOpen={this.state.isOpen}
                  positionTarget={this}
                  positionAt="center botton"
                  zIndex={1002}>

          <ClickOut onClickOut={this.onCloseCalendar}>
            <HiddenDateTimePicker value={value}
                                  onChange={onChange} />
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
