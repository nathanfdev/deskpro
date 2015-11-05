import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import Picker from 'anytime';
import Moment from 'moment';

export class HiddenDateTimePicker extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  componentDidMount() {
    const { value, onChange } = this.props;
    const initial = value ? Moment(value).format('MMMM D, YYYY, hh:mm') : null;

    const picker = new Picker({
      input: ReactDOM.findDOMNode(this.refs.input),
      anchor: ReactDOM.findDOMNode(this),
      format: 'MMMM D, YYYY, hh:mm',
      maxYear: Moment().year(),
      initialValue: initial,
      timeSliders: true
    });

    picker.render();
    picker.show();

    picker.on('change', (newDate) => {
      if (Moment(newDate).isValid()) {
        onChange(Moment(newDate).format('MMMM D, YYYY, hh:mm'));
      } else {
        onChange(null);
      }
    });
  }

  render() {
    return (
      <div>
        <input type="hidden" ref="input" />
      </div>
    );
  }
}
