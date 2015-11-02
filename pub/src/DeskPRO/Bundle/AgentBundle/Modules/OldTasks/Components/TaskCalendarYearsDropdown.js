import React from 'react';
import Moment from 'moment';

const TaskCalendarYearsDropdown = React.createClass({
  mixins: [
    require('react-onclickoutside')
  ],

  handleClickOutside: function() {
    this.props.closeYearDropdown();
  },

  setYear: function(year) {
    this.props.setYear(year);
    this.props.closeYearDropdown();
  },

  render: function() {
    const _this = this;

    const thisYearMoment = new Moment();
    const thisYear = parseInt(thisYearMoment.format('YYYY'), 10);
    const years = [];
    years.push(thisYear);

    for (var i = 1; i <= 5; i++) {
      years.push(thisYear - i);
      years.push(thisYear + i);
    }

    years.sort((a, b) => { return a - b; });

    return (<div className="dpw-value-dropdown" style={{left: this.props.position.x, top: this.props.position.y}}>
        <ul>
          {years.map((year) => {
            return (<li key={'year-' + year}>
                      <a href="#" onClick={_this.setYear.bind(_this, year)}>{year}</a>
                    </li>);
          })}
        </ul>
      </div>);
  }
});

module.exports = TaskCalendarYearsDropdown;
