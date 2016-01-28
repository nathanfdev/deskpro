import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import Picker from 'anytime';
import Moment from 'moment';

export class AbstractDateTimePicker extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func
  };

  componentDidMount() {
    const { value, onChange } = this.props;
    const initial = value ? Moment(value).format('MMMM D, YYYY, hh:mm') : null;

    this.picker = new Picker({
      input: ReactDOM.findDOMNode(this.refs.input),
      anchor: ReactDOM.findDOMNode(this),
      format: 'MMMM D, YYYY, hh:mm',
      maxYear: Moment().year(),
      initialValue: initial,
      timeSliders: true
    });

    this.picker.render();
    this.picker.on('change', newDate => {
      let newValue = null;
      if (Moment(newDate).isValid()) {
        newValue = Moment(newDate).format();
      }
      if (onChange) {
        onChange(newValue);
      }
    });
  }
}
