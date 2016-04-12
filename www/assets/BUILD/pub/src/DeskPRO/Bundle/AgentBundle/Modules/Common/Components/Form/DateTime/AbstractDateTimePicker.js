import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import Moment from 'moment';
import { ConfirmPicker } from './ConfirmPicker';

export class AbstractDateTimePicker extends React.Component {

  static propTypes = {
    value:    PropTypes.string,
    onChange: PropTypes.func,
    onDone:   PropTypes.func
  };

  componentDidMount() {
    const { value, onChange, onDone } = this.props;
    const initial = value ? Moment(value).format('MMMM D, YYYY, hh:mm') : null;

    this.value = initial;
    this.picker = new ConfirmPicker({
      input:        ReactDOM.findDOMNode(this.refs.input),
      anchor:       ReactDOM.findDOMNode(this.refs.anchor || this),
      format:       'MMMM D, YYYY, hh:mm',
      maxYear:      Moment().year(),
      initialValue: initial,
      timeSliders:  true
    });

    this.picker.render();
    this.picker.on('change', newDate => {
      let newValue = null;
      if (Moment(newDate).isValid()) {
        newValue = Moment(newDate).format();
      }
      this.value = newValue;
      if (onChange) {
        onChange(newValue);
      }
    });

    this.picker.on('done', () => {
      onDone(this.value);
    });
  }
}
