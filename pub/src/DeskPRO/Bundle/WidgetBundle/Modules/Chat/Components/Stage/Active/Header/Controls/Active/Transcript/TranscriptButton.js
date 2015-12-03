import React, { PropTypes } from 'react';
import { ControlItem } from '../../ControlItem';

export class TranscriptButton extends React.Component {

  static propTypes = {
    onOpenForm: PropTypes.func
  };

  render() {
    return (
      <ControlItem onClick={this.props.onOpenForm}>
          <span className="dpdesignportal-checkbox-container">
          <span className="dpdesignportal-checkbox"><i className="fa fa-check"></i></span>
          Chat Transcript <i className="fa fa-exclamation-circle"></i>
        </span>
      </ControlItem>
    );
  }
}
