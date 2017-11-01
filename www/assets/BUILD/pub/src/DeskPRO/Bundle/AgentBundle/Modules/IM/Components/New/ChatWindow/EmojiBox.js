import PropTypes from 'prop-types';
import React from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import EmojiPicker from 'emojione-picker';

class EmojiBox extends React.Component
{

  static propTypes = {
    emojiNode:  PropTypes.object,
    emojiClick: PropTypes.func.isRequired,
    clickOut:   PropTypes.func.isRequired,
    isOpen:     PropTypes.bool.isRequired
  };

  render() {
    const { isOpen, emojiNode, clickOut } = this.props;

    const settings = {
      imageType:           'svg',
      sprites:             true,
      imagePathSVGSprites: `./..${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/emoticons/emojione.sprites.svg`
    };

    return (<Detached zIndex={99999} isOpen={isOpen} positionTarget={emojiNode} positionMy="right+25 top+35">
      <ClickOut onClickOut={clickOut} ignoreNodes={['.emoji.trigger']}>
        <div className="emoji box">
          <EmojiPicker emojione={settings} onChange={this.props.emojiClick} />
        </div>
      </ClickOut>
    </Detached>);
  }
}

export default EmojiBox;
