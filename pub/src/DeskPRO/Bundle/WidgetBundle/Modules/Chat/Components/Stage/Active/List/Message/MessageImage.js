import React, { PropTypes } from 'react';

export class MessageImage extends React.Component {

  static propTypes = {
    message: PropTypes.object
  };

  onOpenFullImage = () => {
    console.log('onOpenFullImage');
  };

  render() {
    const { message } = this.props;

    const authorName = message.get('author_name');
    const blob = message.get('metadata').get('blob');
    const downloadUrl = blob.get('download_url');

    return (
      <div className="dpdesignportal-message-content">
        <ul>
          <li className="dpdesignportal-message-asset">
            <div className="dpdesignportal-message-asset attachement-screen">
              <img src={downloadUrl} />
                <div className="dpdesignportal-message-asset-screen-controls">
                  <a href="#"><i className="fa fa-save"></i></a>
                  <a href="#"><i className="fa fa-expand"></i></a>
                  <a href="#"><i className="fa fa-times"></i></a>
                </div>
                <p className="dpdesignportal-message-asset-info">{authorName} attached this photo</p>
                <p className="dpdesignportal-message-asset-cta" onClick={this.onOpenFullImage}>
                  Click here to see the full image
                </p>
            </div>
          </li>
        </ul>
      </div>
    );
  }
}
