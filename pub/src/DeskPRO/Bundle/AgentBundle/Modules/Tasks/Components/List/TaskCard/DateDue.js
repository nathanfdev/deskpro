import React, { PropTypes } from 'react';
import classNames from 'classnames';
import moment from 'moment';
import { HiddenDateTimePicker } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/DateTime/HiddenDateTimePicker';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { DateString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/DateString';

export class DateDue extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func,
    onSetEditing: PropTypes.func
  };

  constructor(props) {
    super(props);

    this.state = {
      isOpen: false
    };
  }

  onOpenCalendar = () => {
    const { onSetEditing } = this.props;
    this.setState({
      isOpen: true
    });

    if (onSetEditing) {
      onSetEditing(true);
    }
  };

  onCloseCalendar = event => {
    event.stopPropagation();
    const { onSetEditing } = this.props;
    this.setState({
      isOpen: false
    });

    if (onSetEditing) {
      onSetEditing(false);
    }
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
                  positionAt="left bottom"
                  zIndex={1002}>

          <ClickOut onClickOut={this.onCloseCalendar}>
            <HiddenDateTimePicker value={value} onChange={onChange} />
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
