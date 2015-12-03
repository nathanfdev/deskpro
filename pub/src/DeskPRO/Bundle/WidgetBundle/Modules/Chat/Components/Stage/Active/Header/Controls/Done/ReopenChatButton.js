import React, { PropTypes } from 'react';
import { ControlItem } from '../ControlItem';

export class ReopenChatButton extends React.Component {

  static propTypes = {
    onReopen: PropTypes.func
  };

  render() {
    return (
      <ControlItem onClick={this.props.onReopen}>
        Reopen Chat <i className="fa fa-commenting-o"></i>
      </ControlItem>
    );
  }
}
