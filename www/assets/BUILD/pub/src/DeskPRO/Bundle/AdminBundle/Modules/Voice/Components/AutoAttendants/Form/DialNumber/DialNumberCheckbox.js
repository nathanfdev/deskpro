import PropTypes from 'prop-types';
import React from 'react';
import { Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';

class DialNumberCheckbox extends React.Component {

  static propTypes = {
    dialNumber: PropTypes.string,
    label:      PropTypes.string,
    value:      PropTypes.bool,
    onChange:   PropTypes.func
  };

  render() {
    const { dialNumber, label, value, onChange } = this.props;

    return (
      <div>
        <div className="dial-number dial-number-checkbox">
          {dialNumber}
        </div>
        <Checkbox
          label={label}
          value={value}
          onChange={onChange}
        />
      </div>
    );
  }
}

export default DialNumberCheckbox;
