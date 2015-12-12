import React, { PropTypes } from 'react';

export class AttachedImage extends React.Component {

  static propTypes = {
    attachment: PropTypes.object,
    onRemove: PropTypes.func
  };

  render() {
    const { attachment, onRemove } = this.props;

    return (
      <li>
        <div className="dpdesignportal-chat-form-attached-image">
          <div className="dpdesignportal-chat-form-attached-image-remove" onClick={() => onRemove(attachment)}>
            <i className="fa fa-times"></i>
          </div>
          <div className="dpdesignportal-chat-form-attached-image-thumb"
               style={{backgroundImage: `url(${attachment.get('download_url')})`}} />
        </div>
      </li>
    );
  }
}
