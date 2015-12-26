import React, { PropTypes } from 'react';
import { openFullImage } from '../../../../../../../../Services/image';

export class MessageImage extends React.Component {

  static propTypes = {
    message: PropTypes.object,
    attachment: PropTypes.object
  };

  openFullImage = () => {
    openFullImage(this.refs.image);
  };

  render() {
    const { message, attachment } = this.props;

    const authorName = message.get('author_name');
    const downloadUrl = attachment.get('download_url');

    return (
      <div>
        <ul>
          <li className="dpdesignportal-message-asset">
            <div className="dpdesignportal-message-asset attachement-screen">
              <img ref="image" src={downloadUrl} onClick={this.openFullImage} />

              <div className="dpdesignportal-message-asset-screen-controls">
                <a href="#"><i className="fa fa-save"></i></a>
                <a href="#"><i className="fa fa-expand"></i></a>
                <a href="#"><i className="fa fa-times"></i></a>
              </div>

              <p className="dpdesignportal-message-asset-info">
                {authorName} attached this photo
              </p>
              <p className="dpdesignportal-message-asset-cta" onClick={this.openFullImage}>
                Click here to see the full image
              </p>
            </div>
          </li>
        </ul>
      </div>
    );
  }
}
