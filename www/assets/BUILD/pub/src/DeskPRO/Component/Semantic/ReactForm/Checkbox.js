import React, { PropTypes } from 'react';
import classNames from 'classnames';

class Checkbox extends React.Component {

  static propTypes = {
    value:    PropTypes.bool,
    label:    PropTypes.string,
    onChange: PropTypes.func
  };

  onClick = () => {
    const { value, onChange } = this.props;
    onChange(!value);
  };

  render() {
    const { value, label } = this.props;

    return (
      <div
        className={classNames('ui', { value }, 'checkbox')}
        onClick={this.onClick}
      >
        <input type="checkbox" checked={value ? 'checked' : ''} className="hidden" />
        <label htmlFor="checkbox">{label}</label>
      </div>
    );
  }
}

export default Checkbox;
