import React, { PropTypes } from 'react';

export class AttachedImage extends React.Component {

  static propTypes = {
    attachment: PropTypes.object
  };

  render() {
    const { attachment } = this.props;

    return (
      <li>
        <div className="dpdesignportal-chat-form-attached-image">
          <div className="dpdesignportal-chat-form-attached-image-remove">
            <i className="fa fa-times"></i>
          </div>
          <div className="dpdesignportal-chat-form-attached-image-thumb"
               style={{backgroundImage: `url(${attachment.get('download_url')})`}} />
        </div>
      </li>
    );
  }
}
