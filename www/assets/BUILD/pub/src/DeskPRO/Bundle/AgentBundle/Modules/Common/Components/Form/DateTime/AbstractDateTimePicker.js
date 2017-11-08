import PropTypes from 'prop-types';
import React from 'react';
import ReactDOM from 'react-dom';
import Picker from 'anytime';
import Moment from 'moment';
import createButton from 'anytime/src/lib/create-button';

Picker.prototype.renderFooter = function (footerEl) {
  // 'Done' button
  var doneBtn = createButton(this.options.doneText, ['anytime-picker__button', 'anytime-picker__button--done']);
  footerEl.appendChild(doneBtn);
  doneBtn.addEventListener('click', function () {
    this.hide();
    this.emit('done', null);
  }.bind(this));

  // 'Clear' button
  var clearBtn = createButton(this.options.clearText, ['anytime-picker__button', 'anytime-picker__button--clear']);
  footerEl.appendChild(clearBtn);
  clearBtn.addEventListener('click', function () {
    this.update(null);
    this.hide();
  }.bind(this));
};

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

    this.picker = new Picker({
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
      onDone && onDone(this.value);
    });
  }
}
