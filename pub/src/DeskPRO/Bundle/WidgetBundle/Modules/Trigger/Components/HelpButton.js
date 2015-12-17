import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class HelpButton extends React.Component {

  static propTypes = {
    type: PropTypes.string,
    onClick: PropTypes.func
  };

  onClick = event => {
    event.preventDefault();
    this.props.onClick();
  };

  render() {
    const { type } = this.props;

    return (
      <div className="dpdesignportal-state-buttons">
        <a href="#" onClick={this.onClick} className={classNames('preemtive-button', {
          'button-s': type === 'small',
          'button-l': type === 'large'
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
