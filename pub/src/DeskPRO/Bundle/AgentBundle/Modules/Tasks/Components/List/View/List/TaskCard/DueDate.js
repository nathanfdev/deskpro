import React, { PropTypes } from 'react';
import classNames from 'classnames';
import ReactDOM from 'react-dom';
import jQuery from 'jquery';
import datetimepicker from 'jquery-ui-timepicker-addon';
import moment from 'moment';

export class DueDate extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  componentDidMount() {
    const { value, onChange } = this.props;

    this.getInput().datetimepicker({
      showButtonPanel: true,
      currentText: value,
      controlType: 'select',
      oneLine: true,
      timeFormat: 'hh:mm tt',
      onSelect: newDate => onChange(newDate)
    });
  }

  componentWillUnmount() {
    this.getInput().destroy();
  }

  onOpenCalendar = () => {
    this.getInput().datetimepicker('show');
  };

  getInput() {
    return jQuery(ReactDOM.findDOMNode(this.refs.calendar));
  }

  render() {
    const { value } = this.props;
    const dateFormatted = value && moment(value).format('hh:mm a');
    const isOverdue = value && moment(value).isBefore();

    return (
      <div className="dpwd--card-line-item" onClick={this.onOpenCalendar}>
        <span className={classNames({'overdue': isOverdue})} onClick={this.onOpenCalendar}>
          <i className="fa fa-calendar-o"/> Due: <input value={value} value={dateFormatted || 'N/A'} ref="calendar" disabled="disabled" />
        </span>
      </div>
    );
  }
}
