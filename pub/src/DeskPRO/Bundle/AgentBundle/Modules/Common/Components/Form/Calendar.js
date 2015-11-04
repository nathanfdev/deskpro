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
    this
      .getInput()
      .datetimepicker({
        showButtonPanel: true,
        controlType: 'select',
        oneLine: true,
        timeFormat: 'hh:mm tt',
        onSelect: newDate => onChange(newDate)
      })
      .datepicker('setDate', value)
    ;
  }

  componentWillUnmount() {
    this.getInput().datepicker('destroy');
  }

  getInput() {
    return jQuery(ReactDOM.findDOMNode(this));
  }

  open() {
    this.getInput().datetimepicker('show');
  }

  render() {
    return (
      <input type="text" disabled="disabled" />
    );
  }
}
