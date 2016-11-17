import React, { PropTypes } from 'react';
import classNames from 'classnames';
import PopUp from 'DeskPRO/Component/Semantic/PopUp/PopUp';
import VoiceMenu from './VoiceMenu';

class VoiceMenuDropdown extends React.Component {

  static propTypes = {
    incomingCall: PropTypes.object
  };

  componentDidMount() {
    const { incomingCall } = this.props;

    if (incomingCall) {
      this.popup.openPopup();
    }
  }

  componentWillReceiveProps(newProps) {
    const { incomingCall } = this.props;

    if (!incomingCall && newProps.incomingCall) {
      this.popup.openPopup();
    }
  }

  closePopup = () => {
    this.popup.closePopup();
  };

  render() {
    const { incomingCall } = this.props;

    return (
      <div className="voice">
        <PopUp
          ref={(c) => { this.popup = c; }}
          positionMy="right top"
          positionAt="right bottom"
          zIndex={99999}
          content={<VoiceMenu {...this.props} />}
          className={classNames('voice-menu-popup', { green: incomingCall })}
        >
          <i className="ui call icon voice-menu-icon" />
        </PopUp>
      </div>
    );
  }
}

export default VoiceMenuDropdown;
