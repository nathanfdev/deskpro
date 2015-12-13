import React, { PropTypes } from 'react';

export class AttachedImage extends React.Component {

  static propTypes = {
    count: PropTypes.number,
    onExpand: PropTypes.func,
    attachment: PropTypes.object,
    onRemove: PropTypes.func
  };

  render() {
    const { count, attachment, onExpand, onRemove } = this.props;

    return (
      <li>
        <div className="dpdesignportal-chat-form-attached-image">
          {count > 1
            ? <div className="dpdesignportal-chat-form-attached-image-count" onClick={onExpand}>
                {count} <i className="fa fa-angle-double-right"></i>
              </div>
            : <div className="dpdesignportal-chat-form-attached-image-remove" onClick={() => onRemove(attachment)}>
                <i className="fa fa-times"></i>
              </div>
          }
          <div className="dpdesignportal-chat-form-attached-image-thumb"
               style={{backgroundImage: `url(${attachment.get('download_url')})`}} />
        </div>
      </li>
    );
  }
}
