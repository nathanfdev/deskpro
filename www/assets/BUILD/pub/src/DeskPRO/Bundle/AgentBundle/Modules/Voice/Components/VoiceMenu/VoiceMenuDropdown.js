import React, { PropTypes } from 'react';
import classNames from 'classnames';
import PopUp from 'DeskPRO/Component/Semantic/PopUp/PopUp';
import VoiceMenu from './VoiceMenu';

class VoiceMenuDropdown extends React.Component {

  static propTypes = {
    reservation: PropTypes.object
  };

  componentDidMount() {
    this.openPopup();
  }

  componentDidUpdate() {
    this.openPopup();
  }

  openPopup = () => {
    const { reservation } = this.props;

    if (reservation) {
      this.popup.openPopup();
    }
  };

  render() {
    const { reservation } = this.props;

    return (
      <div className="voice">
        <PopUp
          ref={(c) => { this.popup = c; }}
          positionMy="right top"
          positionAt="right bottom"
          zIndex={99999}
          content={<VoiceMenu {...this.props} />}
          className={classNames('voice-menu-popup', { green: reservation })}
        >
          <i className="ui call icon voice-menu-icon" />
        </PopUp>
      </div>
    );
  }
}

export default VoiceMenuDropdown;
