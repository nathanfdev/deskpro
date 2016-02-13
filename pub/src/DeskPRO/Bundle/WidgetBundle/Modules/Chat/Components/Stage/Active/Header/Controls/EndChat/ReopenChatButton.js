import React, { PropTypes } from 'react';
import { ControlItem } from '../ControlItem';

export class ReopenChatButton extends React.Component {

  static propTypes = {
    isEnded: PropTypes.bool,
    locked: PropTypes.bool,
    canReopen: PropTypes.bool,
    onReopen: PropTypes.func
  };

  render() {
    const { isEnded, locked, canReopen, onReopen } = this.props;

    return (
      <ControlItem onClick={onReopen} disabled={locked || (isEnded && !canReopen)}>
        Reopen Chat <i className="fa fa-commenting-o"></i>
      </ControlItem>
    );
  }
}
