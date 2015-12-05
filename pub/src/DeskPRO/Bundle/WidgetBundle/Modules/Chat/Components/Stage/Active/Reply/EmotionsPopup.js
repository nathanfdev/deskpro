import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class EmotionsPopup extends React.Component {

  static propTypes = {
    onClick: PropTypes.func
  };

  renderItem(num) {
    return (
      <a onClick={() => this.props.onClick(num)} className="emoticon-link">
        <span className={classNames('emoticon', 'sprite', `sprite-emoticon-${num}`)} />
      </a>);
  }

  render() {
    return (
        <div id="emoticon-panel" className="emoticon-panel">
          <div>
            {this.renderItem(1)}
            {this.renderItem(2)}
            {this.renderItem(3)}
            {this.renderItem(4)}
            {this.renderItem(5)}
          </div>

          <div>
            {this.renderItem(6)}
            {this.renderItem(7)}
            {this.renderItem(8)}
            {this.renderItem(9)}
            {this.renderItem(10)}
          </div>

          <div>
            {this.renderItem(11)}
            {this.renderItem(12)}
            {this.renderItem(13)}
            {this.renderItem(14)}
            {this.renderItem(15)}
          </div>

          <div>
            {this.renderItem(16)}
            {this.renderItem(17)}
            {this.renderItem(18)}
            {this.renderItem(19)}
            {this.renderItem(20)}
          </div>
        </div>
    );
  }
}
