import React, {PropTypes} from 'react';
import Formsy from 'formsy-react';
import ReactDOM from 'react-dom';
import Picker from 'anytime';
import Moment from 'moment';

var DateTimePicker = React.createClass({

  propTypes: {
    name: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    className: PropTypes.string.isRequired,
    initialValue: PropTypes.string,
    filterParams: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    toggleDropdown: PropTypes.func.isRequired
  },

  mixins: [Formsy.Mixin],

  componentDidMount() {
    const {name, initialValue} = this.props;
    const initial = initialValue ? Moment(initialValue).format('MMMM D, YYYY, hh:mm') : null;
    if (initialValue) {
      this.setValue(Moment(initialValue).format('MMMM D, YYYY, hh:mm'));
    }
    // Create the picker
    const picker = new Picker({
      input: ReactDOM.findDOMNode(this.refs[name]),
      button: ReactDOM.findDOMNode(this.refs[name]),
      format: 'MMMM D, YYYY, hh:mm',
      maxYear: Moment().year(),
      initialValue: initial,
      timeSliders: true
    });
    picker.render();

    // Change the component state and submit the edit when the date is changed
    picker.on('change', (newDate) => {
      if (Moment(newDate).isValid()) {
        this.setValue(Moment(newDate).format('MMMM D, YYYY, hh:mm'));
      } else {
        this.setValue(null);
      }
    });
  },

  render() {
    return (
      <div className={this.props.className}>
        <label>{this.props.label}</label>
        <input ref={this.props.name} type="text" value={this.getValue()}/>
      </div>
    );
  }
});

module.exports.DateTimePicker = DateTimePicker;


