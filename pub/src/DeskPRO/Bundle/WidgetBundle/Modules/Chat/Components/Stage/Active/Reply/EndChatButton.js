import React from 'react';
import Simple from 'DeskPRO/Component/Positioned/Simple';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { EndChatConfirm } from './EndChatConfirm';

export class EndChatButton extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      confirmPopup: false
    };
  }

  onOpenPopup = event => {
    event.preventDefault();
    this.setState({
      confirmPopup: true
    });
  };

  onClosePopup = event => {
    event.preventDefault();
    this.setState({
      confirmPopup: false
    });
  };

  render() {
    return (
      <div className="dpdesignportal-chat-form-button-row-end-chat">
        <a href="#" onClick={this.onOpenPopup}>
          <i className="fa fa-upload"></i>End Chat
        </a>

        <Simple isOpen={this.state.confirmPopup}
                positionTarget={this}
                positionAt="right top"
                positionMy="right bottom">

          <ClickOut onClickOut={this.onClosePopup}>
            <EndChatConfirm />
          </ClickOut>
        </Simple>
      </div>
    );
  }
}
