import React, { PropTypes } from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import EmojiPicker from 'emojione-picker';

class EmojiBox extends React.Component
{

  static propTypes = {
    emojiNode:  PropTypes.node,
    emojiClick: PropTypes.func.isRequired,
    clickOut:   PropTypes.func.isRequired,
    isOpen:     PropTypes.bool.isRequired
  };

  render() {
    const { isOpen, emojiNode, clickOut } = this.props;

    const settings = {
      imageType:           'svg',
      sprites:             true,
      imagePathSVGSprites: './../assets/BUILD/pub/build/DeskPRO/Bundle/AgentBundle/Resources/img/emoticons/emojione.sprites.svg'
    };

    return (<Detached isOpen={isOpen} positionTarget={emojiNode} positionMy="right+25 top+35">
      <ClickOut onClickOut={clickOut} ignoreNodes={['.emoji.trigger']}>
        <div className="emoji box">
          <EmojiPicker emojione={settings} onChange={this.props.emojiClick} />
        </div>
      </ClickOut>
    </Detached>);
  }
}

export default EmojiBox;
