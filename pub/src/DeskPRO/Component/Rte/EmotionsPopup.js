import React, { PropTypes } from 'react';
import classNames from 'classnames';
import * as Emotions from './Emotions';

export class EmotionsPopup extends React.Component {

  static propTypes = {
    onClick: PropTypes.func
  };

  renderItem(icon) {
    return (
      <a onClick={() => this.props.onClick(icon)} className="emoticon-link" title={icon}>
        <span className={classNames('emoticon', 'sprite', `sprite-emoticon-${Emotions.SPRITE_MAP[icon]}`)} />
      </a>
    );
  }

  render() {
    return (
        <div id="emoticon-panel" className="emoticon-panel">
          <div>
            {this.renderItem(Emotions.ICON_SMILE)}
            {this.renderItem(Emotions.ICON_BLUSHING)}
            {this.renderItem(Emotions.ICON_WINKING)}
            {this.renderItem(Emotions.ICON_TONGUE)}
            {this.renderItem(Emotions.ICON_TONGUE_2)}
          </div>

          <div>
            {this.renderItem(Emotions.ICON_LAUGHING)}
            {this.renderItem(Emotions.ICON_GRIN)}
            {this.renderItem(Emotions.ICON_EVIL_GREEN)}
            {this.renderItem(Emotions.ICON_DEVIL)}
            {this.renderItem(Emotions.ICON_KIKI)}
          </div>

          <div>
            {this.renderItem(Emotions.ICON_YAWN)}
            {this.renderItem(Emotions.ICON_HEART)}
            {this.renderItem(Emotions.ICON_INLOVE)}
            {this.renderItem(Emotions.ICON_KISS)}
            {this.renderItem(Emotions.ICON_ANGEL)}
          </div>

          <div>
            {this.renderItem(Emotions.ICON_SAD)}
            {this.renderItem(Emotions.ICON_CRY)}
            {this.renderItem(Emotions.ICON_SUPRISED)}
            {this.renderItem(Emotions.ICON_CONFUSED)}
            {this.renderItem(Emotions.ICON_DISAPPOINTED)}
          </div>
        </div>
    );
  }
}
