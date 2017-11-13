import PropTypes from 'prop-types';
import React from 'react';

class Radio extends React.Component {

  static propTypes = {
    label:    PropTypes.string,
    name:     PropTypes.string,
    value:    PropTypes.string,
    onChange: PropTypes.func,
    choice:   PropTypes.string,
    disabled: PropTypes.bool
  };

  render() {
    const { name, label, value, choice, disabled, onChange } = this.props;

    return (
      <div className="ui radio checkbox">
        <input
          className="hidden"
          name={name}
          type="radio"
          value={choice}
          disabled={disabled ? 'disabled' : ''}
          checked={value === choice ? 'checked' : ''}
        />
        <label htmlFor="radio" onClick={() => onChange(choice)}>{label}</label>
      </div>
    );
  }
}

export default Radio;
