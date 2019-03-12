import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class Checkbox extends React.Component {

  static propTypes = {
    className: PropTypes.string,
    disabled:  PropTypes.bool,
    value:     PropTypes.bool,
    label:     PropTypes.string,
    onChange:  PropTypes.func,
    choice:    PropTypes.string
  };

  onClick = () => {
    const { value, choice, onChange, disabled } = this.props;

    if (disabled) {
      return;
    }
    if (choice) {
      onChange(!value ? choice : null);
    } else {
      onChange(!value);
    }
  };

  render() {
    const { className, value, label, choice, disabled } = this.props;

    return (
      <div
        className={classNames('ui', className, { value }, 'checkbox')}
        onClick={this.onClick}
      >
        <input
          onChange={() => {}}
          type="checkbox"
          value={choice}
          checked={value ? 'checked' : ''}
          className="hidden"
          disabled={disabled ? 'disabled' : ''}
        />
        <label htmlFor="checkbox">{label}</label>
      </div>
    );
  }
}

export default Checkbox;
