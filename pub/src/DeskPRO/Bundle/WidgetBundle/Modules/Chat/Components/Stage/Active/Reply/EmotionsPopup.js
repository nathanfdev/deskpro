import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class EmotionsPopup extends React.Component {

  static propTypes = {
    onClick: PropTypes.func
  };

  renderItem(spriteNum, text) {
    return (
      <a onClick={() => this.props.onClick(text)} className="emoticon-link">
        <span className={classNames('emoticon', 'sprite', `sprite-emoticon-${spriteNum}`)} />
      </a>
    );
  }

  render() {
    return (
        <div id="emoticon-panel" className="emoticon-panel">
          <div>
            {this.renderItem(1, ':)')}
            {this.renderItem(2, ':)') /* todo smile code */}
            {this.renderItem(3, ';)')}
            {this.renderItem(4, ':)') /* todo smile code */}
            {this.renderItem(5, ':)') /* todo smile code */}
          </div>

          <div>
            {this.renderItem(6, ':)') /* todo smile code */}
            {this.renderItem(7, ':D')}
            {this.renderItem(8, ':)') /* todo smile code */}
            {this.renderItem(9, ':)') /* todo smile code */}
            {this.renderItem(10, ':)') /* todo smile code */}
          </div>

          <div>
            {this.renderItem(11, ':)') /* todo smile code */}
            {this.renderItem(12, ':)') /* todo smile code */}
            {this.renderItem(13, ':)') /* todo smile code */}
            {this.renderItem(14, ':)') /* todo smile code */}
            {this.renderItem(15, 'O:)')}
          </div>

          <div>
            {this.renderItem(16, ':)') /* todo smile code */}
            {this.renderItem(17, ':)') /* todo smile code */}
            {this.renderItem(18, ':)') /* todo smile code */}
            {this.renderItem(21, ':)') /* todo smile code */}
            {this.renderItem(20, ':|')}
          </div>
        </div>
    );
  }
}
