import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class Checkbox extends React.Component {

  static propTypes = {
    className: PropTypes.string,
    disabled:  PropTypes.bool,
    value:     PropTypes.bool,
    label:     PropTypes.string,
    onChange:  PropTypes.func
  };

  onClick = () => {
    const { value, onChange } = this.props;
    onChange(!value);
  };

  render() {
    const { className, value, label, disabled } = this.props;

    return (
      <div
        className={classNames('ui', className, { value }, 'checkbox')}
        onClick={this.onClick}
      >
        <input
          type="checkbox"
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
