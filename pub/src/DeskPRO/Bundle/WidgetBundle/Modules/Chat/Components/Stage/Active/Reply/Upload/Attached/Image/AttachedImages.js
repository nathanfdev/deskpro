import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import { AttachedImage } from './AttachedImage';

export class AttachedImages extends React.Component {

  static propTypes = {
    attachments: PropTypes.object,
    count: PropTypes.number
  };

  render() {
    const { attachments, count } = this.props;
    const lastImage = attachments.last() || Immutable.fromJS({});

    return (
      <div>
        <div className="dpdesignportal-chat-form-attached-image">
          {count > 1 &&
            <div className="dpdesignportal-chat-form-attached-image-count">
              {count} <i className="fa fa-angle-double-right"></i>
            </div>
          }
          <div className="dpdesignportal-chat-form-attached-image-thumb"
               style={{backgroundImage: `url(${lastImage.get('download_url')})`}} />
        </div>
        <div className="dpdesignportal-chat-form-attached-image-list">
          <ul>
            <AttachedImage attachment={lastImage} />
            <AttachedImage attachment={lastImage} />
            <AttachedImage attachment={lastImage} />
          </ul>
        </div>
      </div>
    );
  }
}
