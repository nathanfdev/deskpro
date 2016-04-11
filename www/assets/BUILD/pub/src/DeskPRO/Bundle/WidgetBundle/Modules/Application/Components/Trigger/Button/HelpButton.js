import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class HelpButton extends React.Component {

  static propTypes = {
    widgetPosition:  PropTypes.string,
    type:            PropTypes.string,
    onClick:         PropTypes.func,
    size:            PropTypes.string,
    name:            PropTypes.string,
    backgroundColor: PropTypes.string,
    textColor:       PropTypes.string,
    borderColor:     PropTypes.string,
    disabled:        PropTypes.bool,
    triggerResize:   PropTypes.func
  };

  componentDidUpdate() {
    this.props.triggerResize();
  }

  onClick = event => {
    event.preventDefault();
    this.props.onClick();
  };

  render() {
    const { widgetPosition, name, size, disabled, backgroundColor, textColor, borderColor } = this.props;

    return (
      <div className="dpdesignportal-state-buttons">
        <a href="#"
           onClick={this.onClick}
           style={{
             backgroundColor,
             color: textColor
           }}
           className={classNames('preemtive-button', {
             disabled,
             'button-s':      size === 'small',
             'button-l':      size === 'large',
             'position-left': widgetPosition === 'bottom.left'
           })}>

          <span className="state-button-text">{name}</span>
          <span className="state-button-icon" style={{
            color: backgroundColor,
            borderColor
          }}>
            <span>?</span>
          </span>
        </a>
      </div>
    );
  }
}
