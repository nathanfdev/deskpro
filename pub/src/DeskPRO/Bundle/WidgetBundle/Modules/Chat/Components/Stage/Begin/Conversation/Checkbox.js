import React, { PropTypes } from 'react';

export class Checkbox extends React.Component {

  static propTypes = {
    value: PropTypes.bool,
    onToggle: PropTypes.func.isRequired
  };

  render() {
    return (
      <span className="checkbox-container">
        <span className="user-collect-checkbox checked"><i className="fa fa-check"></i></span>
        <span className="checkbox-text"> I prefer not to say</span>
      </span>
    );
  }
}
