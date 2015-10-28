import React, {Component} from 'react';
import Formsy from 'formsy-react';
import ReactDOM from 'react-dom';
import Picker from 'anytime';
import Moment from 'moment';

var DateTimePicker = React.createClass({
  mixins: [Formsy.Mixin],

  componentDidMount() {
    const {name} = this.props;
    // Create the picker
    const picker = new Picker({
      input: ReactDOM.findDOMNode(this.refs[name]),
      button: ReactDOM.findDOMNode(this.refs[name]),
      format: 'hh:mm, MMMM D, YYYY'
    });
    picker.render();

    // Change the component state and submit the edit when the date is changed
    picker.on('change', (newDate) => {
      console.log('Changed', newDate);
      this.setValue(Moment(newDate).format('hh:mm, MMMM D, YYYY'));
    });
  },

  render() {
    return (
      <div className={this.props.className}>
        <label>{this.props.name}</label>
        <input ref={this.props.name} type="text" value={this.getValue()}/>
      </div>
    );
  }
});

module.exports.DateTimePicker = DateTimePicker;


