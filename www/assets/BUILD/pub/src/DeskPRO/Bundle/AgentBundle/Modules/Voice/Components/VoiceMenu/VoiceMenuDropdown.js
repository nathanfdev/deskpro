import React, { PropTypes } from 'react';
import classNames from 'classnames';
import PopUp from 'DeskPRO/Component/Semantic/PopUp/PopUp';
import VoiceMenuContainer from './VoiceMenuContainer';

class VoiceMenuDropdown extends React.Component {

  static propTypes = {
    callFrom:   PropTypes.object,
    callTarget: PropTypes.object
  };

  render() {
    const { callFrom, callTarget  } = this.props;

    return (
      <PopUp
        positionMy="left top"
        positionAt="left bottom"
        zIndex={99999}
        content={<VoiceMenuContainer {...this.props} />}
        className={classNames('voice-menu-popup', { green: callFrom || callTarget })}
      >
        <button>Button</button>
      </PopUp>
    );
  }
}

export default VoiceMenuDropdown;
