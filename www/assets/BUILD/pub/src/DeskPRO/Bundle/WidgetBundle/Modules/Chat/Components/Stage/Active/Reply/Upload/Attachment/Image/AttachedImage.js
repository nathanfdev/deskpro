import PropTypes from 'prop-types';
import React from 'react';
import { openFullImage } from 'DeskPRO/Component/Util/FullImage';

export class AttachedImage extends React.Component {

  static propTypes = {
    count:      PropTypes.number,
    onExpand:   PropTypes.func,
    attachment: PropTypes.object,
    onRemove:   PropTypes.func
  };

  openFullImage = () => {
    openFullImage(this.refs.image);
  };

  render() {
    const { count, attachment, onExpand, onRemove } = this.props;
    const downloadUrl = attachment.get('download_url');

    return (
      <li>
        <div className="dpdesignportal-chat-form-attached-image">
          {count > 1
            ? <div className="dpdesignportal-chat-form-attached-image-count" onClick={onExpand}>
                {count} <i className="fa fa-angle-double-right" />
            </div>
            : <div className="dpdesignportal-chat-form-attached-image-remove" onClick={() => onRemove(attachment)}>
              <i className="fa fa-times" />
            </div>
          }
          <div
            className="dpdesignportal-chat-form-attached-image-thumb"
            onClick={this.openFullImage}
            style={{ backgroundImage: `url(${downloadUrl})` }}
          >
          </div>
          <img role="presentation" src={downloadUrl} ref="image" className="hidden" />
        </div>
      </li>
    );
  }
}
