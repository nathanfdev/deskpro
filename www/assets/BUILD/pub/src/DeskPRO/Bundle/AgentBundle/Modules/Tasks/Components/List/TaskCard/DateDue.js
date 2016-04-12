import React, { PropTypes } from 'react';
import classNames from 'classnames';
import moment from 'moment';
import { HiddenDateTimePicker } from '../../../../Common/Components/Form/DateTime/HiddenDateTimePicker';
import { Detached as Positioned } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { CardWidget } from './CardWidget';

export class DateDue extends CardWidget {

  static propTypes = {
    value:             PropTypes.string,
    onChange:          PropTypes.func.isRequired,
    onSetEditing:      PropTypes.func,
    openBySingleClick: PropTypes.bool
  };

  render() {
    const { value } = this.state;
    const isOverdue = value && moment(value).isBefore();
    const prop = { [this.props.openBySingleClick ? 'onClick' : 'onDoubleClick']: this.onOpen };

    let title = 'N/A';
    if (value) {
      const dueMoment = moment(value);
      if (dueMoment.isSame(moment(), 'day')) {
        title = 'Today, ';
      } else if (dueMoment.isSame(moment().subtract(1, 'days'))) {
        title = 'Yesterday, ';
      } else {
        title = dueMoment.format('MMM Do YYYY, ');
      }
      title += dueMoment.format('hh:mm a');
    }

    return (
      <div className="dpwd--card-line-item" style={{ display: 'inline-block', maxWidth: '30%' }}>
        <div
          {...prop}
          className={classNames({ overdue: isOverdue })}
          ref="trigger"
          style={{
            display:     'inline-block',
            position:    'relative',
            paddingLeft: 20,
            overflow:    'hidden',
            width:       '100%'
          }}
          >
          <i className="fa fa-calendar-o" style={{ position: 'absolute', left: 2, top: 2 }} />
          <span title={title}>Due: {title}</span>
        </div>
        <Positioned
          isOpen={this.state.isOpen}
          positionTarget={this}
          positionAt="left bottom"
          collision="fit"
          zIndex={1002}
          >
          <ClickOut onClickOut={this.onClose} ignoreNodes={[this.refs.trigger]}>
            <HiddenDateTimePicker value={value} onDone={this.props.onChange} />
          </ClickOut>
        </Positioned>
      </div>
    );
  }
}
