import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import moment from 'moment';
import { HiddenDateTimePicker } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/DateTime/HiddenDateTimePicker';
import { Detached as Positioned } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { DateString } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/DateString';
import { CardWidget } from './CardWidget';

export class DateDue extends CardWidget {

  static propTypes = {
    value:             PropTypes.string,
    onChange:          PropTypes.func.isRequired,
    onSetEditing:      PropTypes.func,
    openBySingleClick: PropTypes.bool,
    withoutIcon:       PropTypes.bool,
    className:         PropTypes.string
  };

  render() {
    const { value } = this.state;
    const { withoutIcon } = this.props;
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

    const style = {
      display:      'inline-block',
      position:     'relative',
      paddingLeft:  20,
      overflow:     'hidden',
      width:        '100%',
      textOverflow: 'ellipsis'
    };

    return (
      <div className={classNames('dpwd--card-line-item', this.props.className)}>

        {withoutIcon
          ? <span title={title} ref="trigger" {...prop}>{title}</span>
          : <div className={classNames({ overdue: isOverdue })} {...prop} ref="trigger" {...prop} style={style}>
              <i className="fa fa-calendar-o" style={{ position: 'absolute', left: 2, top: 2 }} />
              <span title={title}>Due: {title}</span>
            </div>
        }

        <Positioned isOpen={this.state.isOpen} positionTarget={this} positionAt="left bottom" collision="fit"
          zIndex={1002}
        >

          <ClickOut onClickOut={this.onClose} ignoreNodes={[this.refs.trigger]}>
            <HiddenDateTimePicker value={value} onDone={this.onChange} />
          </ClickOut>
        </Positioned>
      </div>
    );
  }
}
