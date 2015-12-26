import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class HelpButton extends React.Component {

  static propTypes = {
    type: PropTypes.string,
    onClick: PropTypes.func,
    size: PropTypes.string,
    disabled: PropTypes.bool
  };

  onClick = event => {
    event.preventDefault();
    this.props.onClick();
  };

  render() {
    const { size, disabled } = this.props;

    return (
      <div className="dpdesignportal-state-buttons">
        <a href="#" onClick={this.onClick} className={classNames('preemtive-button', {
          'button-s': size === 'small',
          'button-l': size === 'large',
          'disabled': disabled
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
