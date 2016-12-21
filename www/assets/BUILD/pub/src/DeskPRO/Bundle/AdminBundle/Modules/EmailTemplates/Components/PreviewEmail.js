import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { MimeIcon } from 'DeskPRO/Component/Semantic/Icon';
import { filenameMaxLength } from 'DeskPRO/Component/Util/Filename';

class PreviewEmail extends React.Component {
  static propTypes = {
    preview: PropTypes.object
  };

  getPreviewAttachments = () => {
    if (!this.props.preview || !this.props.preview.get('attachments')) {
      return null;
    }
    const attachments = this.props.preview.get('attachments').valueSeq().map((attachment, key) =>
      <div className="card" key={key}>
        <div className="content">
          <a href={`${attachment.get('download_url')}?dl=1`}>
            <div className="icon">
              <MimeIcon mimeType={attachment.get('content_type')} className="huge" /><br />
            </div>
            <span className="filename">{filenameMaxLength(attachment.get('filename'), 18)}</span><br />
            <span className="filesize">{attachment.get('filesize_readable')}</span>
          </a>
        </div>
      </div>
    );
    return (<div className="attachments">
      <div className="ui cards">
        {attachments}
      </div>
    </div>);
  };

  render() {
    let code = 'Preview';
    let attachments = null;
    if (this.props.preview) {
      code = this.props.preview.get('body');
      attachments = this.props.preview.get('attachments');
    }

    return (
      <div className={classNames('email-preview', { 'with-attachment': attachments })}>
        <div className="email" dangerouslySetInnerHTML={{ __html: code }} />
        {this.getPreviewAttachments()}
      </div>
    );
  }
}
export default PreviewEmail;
