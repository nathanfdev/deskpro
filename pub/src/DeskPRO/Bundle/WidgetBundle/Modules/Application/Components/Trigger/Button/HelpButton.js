import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class HelpButton extends React.Component {

  static propTypes = {
    type: PropTypes.string,
    onClick: PropTypes.func,
    size: PropTypes.string,
    name: PropTypes.string,
    backgroundColor: PropTypes.string,
    textColor: PropTypes.string,
    borderColor: PropTypes.string,
    disabled: PropTypes.bool
  };

  onClick = event => {
    event.preventDefault();
    this.props.onClick();
  };

  render() {
    const { name, size, disabled, backgroundColor, textColor, borderColor } = this.props;

    return (
      <div className="dpdesignportal-state-buttons">
        <a href="#"
           onClick={this.onClick}
           style={{
             backgroundColor: backgroundColor,
             color: textColor
           }}
           className={classNames('preemtive-button', {
             'button-s': size === 'small',
             'button-l': size === 'large',
             'disabled': disabled
           })}>

          <span className="state-button-text">{name}</span>
          <span className="state-button-icon" style={{
            color: borderColor,
            borderColor: borderColor
          }}>
            <span>?</span>
          </span>
        </a>
      </div>
    );
  }
}
