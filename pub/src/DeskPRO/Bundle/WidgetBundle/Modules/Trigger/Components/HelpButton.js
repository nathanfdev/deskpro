import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class HelpButton extends React.Component {

  static propTypes = {
    type: PropTypes.string,
    onClick: PropTypes.func,
    large: PropTypes.bool,
    small: PropTypes.bool
  };

  onClick = event => {
    event.preventDefault();
    this.props.onClick();
  };

  render() {
    const { small, large } = this.props;

    return (
      <div className="dpdesignportal-state-buttons">
        <a href="#" onClick={this.onClick} className={classNames('preemtive-button', {
          'button-s': small,
          'button-l': large
        })}>

          <span className="state-button-text">Help</span>
          <span className="state-button-icon">
            <span>?</span>
          </span>
        </a>
      </div>
    );
  }
}
