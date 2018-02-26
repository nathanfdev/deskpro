import React from 'react';
import PropTypes from 'prop-types';
import { Datepicker as BaseDatepicker } from '@deskpro/react-components';

class DatePicker extends React.Component {

  static propTypes = {
    onChange: PropTypes.func
  };

  render() {
    return <BaseDatepicker onSelect={this.props.onChange} {...this.props} />;
  }
}

export default DatePicker;
