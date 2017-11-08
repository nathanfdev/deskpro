import PropTypes from 'prop-types';
import React from 'react';

export class ReplyButtons extends React.Component {

  static propTypes = {
    backgroundColor:      PropTypes.string,
    textColor:            PropTypes.string,
    onClick:              PropTypes.func,
    helpPopupStartButton: PropTypes.string
  };

  onClick = (event) => {
    event.preventDefault();
    this.props.onClick();
  };

  render() {
    const { backgroundColor, textColor, helpPopupStartButton } = this.props;

    return (
      <div className="preemtive-chat-footer">
        <div className="preemtive-chat-footer-button">
          <a
            href="#chat"
            onClick={this.onClick}
            style={{
              backgroundColor,
              color: textColor
            }}
          >
            {helpPopupStartButton}
          </a>
        </div>
      </div>
    );
  }
}
