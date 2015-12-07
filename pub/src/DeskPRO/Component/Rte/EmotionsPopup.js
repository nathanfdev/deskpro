import React, { PropTypes } from 'react';
import classNames from 'classnames';
import * as Emotions from './Emotions';

export class EmotionsPopup extends React.Component {

  static propTypes = {
    onClick: PropTypes.func
  };

  renderItem(icon) {
    return (
      <a onClick={() => this.props.onClick(icon)} className="emoticon-link">
        <span className={classNames('emoticon', 'sprite', `sprite-emoticon-${Emotions.SPRITE_MAP[icon]}`)} />
      </a>
    );
  }

  render() {
    return (
        <div id="emoticon-panel" className="emoticon-panel">
          <div>
            {this.renderItem(Emotions.ICON_SMILE)}
            {this.renderItem(2)}
            {this.renderItem(Emotions.ICON_WINKING)}
            {this.renderItem(4)}
            {this.renderItem(5)}
          </div>

          <div>
            {this.renderItem(6)}
            {this.renderItem(Emotions.ICON_BIG_GRIN)}
            {this.renderItem(8)}
            {this.renderItem(9)}
            {this.renderItem(10)}
          </div>

          <div>
            {this.renderItem(11)}
            {this.renderItem(12)}
            {this.renderItem(13)}
            {this.renderItem(14)}
            {this.renderItem(Emotions.ICON_ANGEL)}
          </div>

          <div>
            {this.renderItem(16)}
            {this.renderItem(17)}
            {this.renderItem(18)}
            {this.renderItem(19)}
            {this.renderItem(Emotions.ICON_DISAPPOINTED)}
          </div>
        </div>
    );
  }
}
