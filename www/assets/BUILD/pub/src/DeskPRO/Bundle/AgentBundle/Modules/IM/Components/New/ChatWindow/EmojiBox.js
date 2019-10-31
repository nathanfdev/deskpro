import PropTypes from 'prop-types';
import React from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import EmojiPicker from 'emojione-picker';
import classNames from 'classnames';

class EmojiBox extends React.Component {

  static propTypes = {
    emojiNode:  PropTypes.object,
    emojiClick: PropTypes.func.isRequired,
    clickOut:   PropTypes.func.isRequired,
    isOpen:     PropTypes.bool.isRequired
  };

  get getPositionMy() {
    return this.hasOpenToTop ? 'right+25 bottom-25' : 'right+25 top+25';
  }

  get hasOpenToTop() { // eslint-disable-line
    const el = document.querySelector('.im.chat.drawer');
    const rect = el.getBoundingClientRect();

    return ((window.innerHeight || document.documentElement.clientHeight) - rect.bottom) < 220;
  }

  render() {
    const { isOpen, emojiNode, clickOut } = this.props;

    const settings = {
      imageType:           'svg',
      sprites:             true,
      imagePathSVGSprites: `./..${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/emoticons/emojione.sprites.svg`
    };

    return (<Detached zIndex={99999} isOpen={isOpen} positionTarget={emojiNode} positionMy={this.getPositionMy}>
      <ClickOut onClickOut={clickOut} ignoreNodes={['.emoji.trigger']}>
        <div className={classNames('emoji box', { 'arrow-bottom': this.hasOpenToTop })}>
          <EmojiPicker emojione={settings} onChange={this.props.emojiClick} />
        </div>
      </ClickOut>
    </Detached>);
  }
}

export default EmojiBox;
