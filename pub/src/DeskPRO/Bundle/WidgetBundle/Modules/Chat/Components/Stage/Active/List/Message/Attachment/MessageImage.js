import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';

export class MessageImage extends React.Component {

  static propTypes = {
    message: PropTypes.object,
    attachment: PropTypes.object
  };

  onOpenFullImage = () => {
    const image = ReactDOM.findDOMNode(this.refs.image);
    const downloadUrl = this.props.attachment.get('download_url');

    const width = image.naturalWidth < 800 ? image.naturalWidth : 800;
    const height = image.naturalHeight < 800 ? image.naturalHeight : 800;

    const left = (screen.width / 2) - (width / 2);
    const top = (screen.height / 2) - (height / 2);

    window.open(
      downloadUrl,
      'Image',

      `width=${width},height=${height},left=${left},top=${top},` +
      `resizable=1,directories=0,titlebar=0,location=0,status=0,toolbar=0,menubar=0`
    );
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
              <img ref="image" src={downloadUrl} />

              <div className="dpdesignportal-message-asset-screen-controls">
                <a href="#"><i className="fa fa-save"></i></a>
                <a href="#"><i className="fa fa-expand"></i></a>
                <a href="#"><i className="fa fa-times"></i></a>
              </div>

              <p className="dpdesignportal-message-asset-info">
                {authorName} attached this photo
              </p>
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
