import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import classNames from 'classnames';
import moment from 'moment';
import Picker from 'anytime';

export class Due extends React.Component {

  static propTypes = {
    date: PropTypes.string
  };

  componentDidMount() {
    const { date } = this.props;
    const initial = date ? moment(date).format() : null;
    const picker = new Picker({
      input: ReactDOM.findDOMNode(this.refs.calendar),
      button: ReactDOM.findDOMNode(this.refs.button),
      initialValue: initial,
      format: 'hh:mm, MMMM D, YYYY'
    });

    picker.render();
    picker.on('change', newDate => {
      console.log(newDate);
    });
  }

  render() {
    const { date } = this.props;
    const dateFormatted = date && moment(date).format('hh:mm a');
    const isOverdue = date && moment(date).isBefore();

    return (
      <span className={classNames('dpwd--card-line-item', {'overdue': isOverdue})} ref="button">
        <i className="fa fa-calendar-o"/> Due: {dateFormatted || 'N/A'}
        <input type="text"
               name="due-date"
               className="due-date-field"
               disabled="disabled" ref="calendar" />
      </span>
    );
  }
}
