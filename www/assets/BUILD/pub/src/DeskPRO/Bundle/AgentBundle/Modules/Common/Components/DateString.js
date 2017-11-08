import PropTypes from 'prop-types';
import React from 'react';
import moment from 'moment';

export class DateString extends React.Component {

  static propTypes = {
    value: PropTypes.string
  };

  render() {
    const { value } = this.props;
    let string = 'N/A';

    if (value) {
      const dueMoment = moment(value);
      if (dueMoment.isSame(moment(), 'day')) {
        string = 'Today, ';
      } else if (dueMoment.isSame(moment().subtract(1, 'days'))) {
        string = 'Yesterday, ';
      } else {
        string = dueMoment.format('MMM Do YYYY, ');
      }

      string += dueMoment.format('hh:mm a');
    }

    return (
      <span>
        {string}
      </span>
    );
  }

}
