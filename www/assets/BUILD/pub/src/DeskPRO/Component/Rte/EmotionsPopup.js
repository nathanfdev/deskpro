import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { smiles, spriteMap } from './Emotions';

export class EmotionsPopup extends React.Component {

  static propTypes = {
    onClick: PropTypes.func
  };

  renderItem(code) {
    return (
      <a onClick={() => this.props.onClick(code)} className="emoticon-link" title={code}>
        <span className={classNames('emoticon', 'sprite', `sprite-emoticon-${spriteMap[code]}`)} />
      </a>
    );
  }

  render() {
    return (
      <div id="emoticon-panel" className="emoticon-panel">
        <div>
          {this.renderItem(smiles.ICON_SMILE)}
          {this.renderItem(smiles.ICON_BLUSHING)}
          {this.renderItem(smiles.ICON_WINKING)}
          {this.renderItem(smiles.ICON_TONGUE)}
          {this.renderItem(smiles.ICON_TONGUE_2)}
        </div>

        <div>
          {this.renderItem(smiles.ICON_LAUGHING)}
          {this.renderItem(smiles.ICON_GRIN)}
          {this.renderItem(smiles.ICON_EVIL_GREEN)}
          {this.renderItem(smiles.ICON_DEVIL)}
          {this.renderItem(smiles.ICON_KIKI)}
        </div>

        <div>
          {this.renderItem(smiles.ICON_YAWN)}
          {this.renderItem(smiles.ICON_HEART)}
          {this.renderItem(smiles.ICON_INLOVE)}
          {this.renderItem(smiles.ICON_KISS)}
          {this.renderItem(smiles.ICON_ANGEL)}
        </div>

        <div>
          {this.renderItem(smiles.ICON_SAD)}
          {this.renderItem(smiles.ICON_CRY)}
          {this.renderItem(smiles.ICON_SUPRISED)}
          {this.renderItem(smiles.ICON_CONFUSED)}
          {this.renderItem(smiles.ICON_DISAPPOINTED)}
        </div>
      </div>
    );
  }
}
