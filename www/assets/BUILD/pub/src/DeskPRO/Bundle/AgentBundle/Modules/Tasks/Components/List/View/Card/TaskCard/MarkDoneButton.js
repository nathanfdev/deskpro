import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class MarkDoneButton extends React.Component {

  static propTypes = {
    isDone:   PropTypes.bool,
    onToggle: PropTypes.func
  };

  render() {
    const { onToggle, isDone } = this.props;

    return (
      <div
        className={classNames('dpw--single-card-mark-done', { 'dpw--single-card-mark-done-minimized': isDone })}
        onClick={onToggle}
        >
        <span>{isDone ? 'Done' : 'Mark Done'}</span>
        <i className="fa fa-check" />
      </div>
    );
  }
}
