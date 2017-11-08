import PropTypes from 'prop-types';
import React from 'react';
import BaseToggle from '../Form/Toggle';

class Toggle extends React.Component {

  static propTypes = {
    value:    PropTypes.bool,
    onChange: PropTypes.func
  };

  render() {
    const { value, onChange } = this.props;

    return (
      <BaseToggle
        {...this.props}
        active={value}
        onChange={onChange}
      />
    );
  }
}

export default Toggle;
