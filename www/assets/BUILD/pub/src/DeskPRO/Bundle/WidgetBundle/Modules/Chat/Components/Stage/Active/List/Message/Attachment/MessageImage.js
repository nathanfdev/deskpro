import React, { PropTypes } from 'react';
import { openFullImage } from 'DeskPRO/Component/Util/FullImage';

export class MessageImage extends React.Component {

  static propTypes = {
    authorName: PropTypes.string,
    attachment: PropTypes.object
  };

  openFullImage = () => {
    openFullImage(this.refs.image);
  };

  render() {
    const { authorName, attachment } = this.props;
    const downloadUrl = attachment.get('download_url');

    return (
      <div>
        <ul>
          <li className="dpdesignportal-message-asset">
            <div className="dpdesignportal-message-asset attachement-screen">
              <img ref="image" src={downloadUrl} onClick={this.openFullImage} />

              <div className="dpdesignportal-message-asset-screen-controls">
                <a href="#"><i className="fa fa-save" /></a>
                <a href="#"><i className="fa fa-expand" /></a>
                <a href="#"><i className="fa fa-times" /></a>
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
