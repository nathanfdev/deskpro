import React, { PropTypes } from 'react';

export class AttachedImages extends React.Component {

  static propTypes = {
    count: PropTypes.number
  };

  render() {
    const { count } = this.props;

    return (
      <div className="dpdesignportal-chat-form-attached-image">
        <div className="dpdesignportal-chat-form-attached-image-count">
          {count} <i className="fa fa-angle-double-right"></i>
        </div>
        <div className="dpdesignportal-chat-form-attached-image-thumb" />
      </div>
    );
  }
}
