import PropTypes from 'prop-types';
import React from 'react';
import moment from 'moment';
import classNames from 'classnames';

export class YearDropdown extends React.Component {

  static propTypes = {
    date:     PropTypes.object.isRequired,
    onChange: PropTypes.func.isRequired
  };

  onChange = (event, year) => {
    event.preventDefault();

    const { date, onChange } = this.props;
    onChange(date.year(year));
  };

  render() {
    const selectedYear = this.props.date.year();
    const thisYear = moment().year();
    const years = [thisYear];

    for (let num = 1; num <= 5; num++) {
      years.unshift(thisYear - num);
      years.push(thisYear + num);
    }

    return (
      <div className="dpw-value-dropdown">
        <ul>
          {years.map(year =>
            <li key={year}>
              <a href="#"
                className={classNames({ 'active': selectedYear === year })}
                onClick={event => this.onChange.bind(this, event, year)()}
              >

                {year}
              </a>
            </li>
          )}
        </ul>
      </div>
    );
  }
}
