import React, { PropTypes } from 'react';
import { ControlItem } from '../ControlItem';
import classNames from 'classnames';

export class TranscriptButton extends React.Component {

  static propTypes = {
    active: PropTypes.bool,
    onClick: PropTypes.func
  };

  render() {
    const { active, onClick } = this.props;

    return (
      <ControlItem onClick={onClick}>
          <span className="dpdesignportal-checkbox-container">
          <span className={classNames('dpdesignportal-checkbox', {'active': active})}>
            <i className="fa fa-check"></i>
          </span>
          Chat Transcript <i className="fa fa-exclamation-circle"></i>
        </span>
      </ControlItem>
    );
  }
}
