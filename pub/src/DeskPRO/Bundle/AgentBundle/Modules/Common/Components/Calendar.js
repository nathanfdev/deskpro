import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import jQuery from 'jquery';
import datetimepicker from 'jquery-ui-timepicker-addon';

export class Calendar extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  componentDidMount() {
    const { value, onChange } = this.props;
    const $datePicker = jQuery(ReactDOM.findDOMNode(this));

    $datePicker.datetimepicker({
      showButtonPanel: true,
      currentText: value,
      controlType: 'select',
      oneLine: true,
      timeFormat: 'hh:mm tt',
      onSelect: newDate => onChange(newDate)
    });
  }

  render() {
    return (
      <div />
    );
  }
}
