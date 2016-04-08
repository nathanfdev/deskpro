import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class Checkbox extends React.Component {

  static propTypes = {
    value:    PropTypes.bool,
    onToggle: PropTypes.func.isRequired
  };

  render() {
    const { value, onToggle } = this.props;

    return (
      <span className="checkbox-container" onClick={onToggle}>
        <span className={classNames('user-collect-checkbox', { checked: value })}>
          <i className="fa fa-check" />
        </span>
        <span className="checkbox-text"> I prefer not to say</span>
      </span>
    );
  }
}
