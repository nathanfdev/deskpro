import React, { PropTypes } from 'react';
import classNames from 'classnames';
import moment from 'moment';
import { HiddenDateTimePicker } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/DateTime/HiddenDateTimePicker';
import { Simple as Positioned } from 'DeskPRO/Component/Positioned/Simple';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { DateString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/DateString';
import { CardWidget } from './CardWidget';

export class DateDue extends CardWidget {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired,
    onSetEditing: PropTypes.func,
    openBySingleClick: PropTypes.bool
  };

  render() {
    const { value } = this.state;
    const isOverdue = value && moment(value).isBefore();
    const prop = {[this.props.openBySingleClick ? 'onClick' : 'onDoubleClick']: this.onOpen};

    return (
      <div ref="trigger" className="dpwd--card-line-item" {...prop}>
        <span className={classNames({'overdue': isOverdue})}>
          <i className="fa fa-calendar-o"/>
          <i />

          Due: <DateString value={value} />
        </span>
        <Positioned isOpen={this.state.isOpen}
                  positionTarget={this}
                  positionAt="left bottom"
                  collision="fit"
                  zIndex={1002}>

          <ClickOut onClickOut={this.onClose} ignoreNodes={[this.refs.trigger]}>
            <HiddenDateTimePicker value={value} onDone={this.onChange} />
          </ClickOut>
        </Positioned>
      </div>
    );
  }
}
