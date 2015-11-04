import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import jQuery from 'jquery';
import datepicker from 'jquery-ui/datepicker';

export class Calendar extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  componentDidMount() {
    const { value, onChange } = this.props;
    const $datePicker = jQuery(ReactDOM.findDOMNode(this));

    $datePicker.datepicker({
      showButtonPanel: true,
      currentText: value,
      onSelect: newDate => {
        onChange(newDate);
      }
    });
  }

  render() {
    return (
      <div />
    );
  }
}
