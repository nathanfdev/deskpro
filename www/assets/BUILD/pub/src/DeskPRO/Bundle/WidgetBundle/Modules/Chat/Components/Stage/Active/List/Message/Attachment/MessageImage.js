import PropTypes from 'prop-types';
import React from 'react';
import { openFullImage } from 'DeskPRO/Component/Util/FullImage';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

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
              <img role="presentation" ref="image" src={downloadUrl} onClick={this.openFullImage} />

              <div className="dpdesignportal-message-asset-screen-controls">
                <a href="#"><i className="fa fa-save" /></a>
                <a href="#"><i className="fa fa-expand" /></a>
                <a href="#"><i className="fa fa-times" /></a>
              </div>

              <p className="dpdesignportal-message-asset-info">
                {portalPhrases.get('portal.chat.attached_photo', { '{authorName}': authorName })}
              </p>
              <p className="dpdesignportal-message-asset-cta" onClick={this.openFullImage}>
                {portalPhrases.get('portal.chat.see_full_image')}
              </p>
            </div>
          </li>
        </ul>
      </div>
    );
  }
}
