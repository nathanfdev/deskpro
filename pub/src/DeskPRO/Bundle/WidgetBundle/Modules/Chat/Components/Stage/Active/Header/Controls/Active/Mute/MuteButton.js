import React, { PropTypes } from 'react';
import { ControlItem } from '../../ControlItem';

export class MuteButton extends React.Component {

  static propTypes = {
    onClick: PropTypes.func
  };

  render() {
    return (
      <ControlItem className="dpdesignportal-chat-header-control-mute" onClick={this.props.onClick}>
        <i className="fa fa-volume-up"></i>Mute
      </ControlItem>
    );
  }
}
