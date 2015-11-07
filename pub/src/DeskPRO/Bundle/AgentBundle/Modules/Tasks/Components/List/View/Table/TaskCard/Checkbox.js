import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class Checkbox extends React.Component {

  static propTypes = {
    selected: PropTypes.bool,
    onToggle: PropTypes.func.isRequired
  };

  render() {
    const { selected, onToggle } = this.props;

    return (
      <span className="checkbox" onClick={onToggle}>
        <i className={classNames('fa', 'fa-check', {'selected': selected})} />
      </span>
    );
  }
}
