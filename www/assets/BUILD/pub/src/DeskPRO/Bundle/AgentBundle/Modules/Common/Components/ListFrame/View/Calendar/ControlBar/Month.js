import PropTypes from 'prop-types';
import React from 'react';

export class Month extends React.Component {

  static propTypes = {
    date:     PropTypes.object.isRequired,
    onChange: PropTypes.func.isRequired
  };

  onPreviousMonth = () => {
    const { date, onChange } = this.props;
    onChange(date.subtract(1, 'months'));
  };

  onNextMonth = () => {
    const { date, onChange } = this.props;
    onChange(date.add(1, 'months'));
  };

  render() {
    return (
      <div className="dpwd-calendar-controls-month">
        <span className="dpwd-calendar-controls-month-last" onClick={this.onPreviousMonth}>
          <i className="fa fa-caret-left" />
        </span>
        <span className="dpwd-calendar-controls-month-text">{this.props.date.format('MMMM')}</span>
        <span className="dpwd-calendar-controls-month-next" onClick={this.onNextMonth}>
          <i className="fa fa-caret-right" />
        </span>
      </div>
    );
  }
}
