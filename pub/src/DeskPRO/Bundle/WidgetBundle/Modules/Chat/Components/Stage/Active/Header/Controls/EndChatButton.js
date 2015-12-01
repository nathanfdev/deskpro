import React, { PropTypes } from 'react';
import { ControlItem } from './ControlItem';

export class EndChatButton extends React.Component {

  static propTypes = {
    onOpenPopup: PropTypes.func
  };

  render() {
    return (
      <ControlItem onClick={this.props.onOpenPopup}>
        End Chat <i className="fa fa-power-off"></i>
      </ControlItem>
    );
  }
}
