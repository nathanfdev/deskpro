import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { MimeIcon } from 'DeskPRO/Component/Semantic/Icon';
import { filenameMaxLength } from 'DeskPRO/Component/Util/Filename';
import SimpleFrame from 'Ampliflux/common/components/SimpleFrame';

class PreviewEmail extends React.Component {
  static propTypes = {
    preview:     PropTypes.object,
    type:        PropTypes.string,
    content:     PropTypes.string,
    name:        PropTypes.string,
    newTemplate: PropTypes.bool,
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
    let code;
    if (this.props.newTemplate) {
      code = 'Preview will be available when you add content';
    } else if (this.props.content || this.props.name) {
      code = 'Loading preview';
    } else {
      code = 'Select a template to preview';
    }
    let attachments = null;
    if (this.props.preview) {
      code = this.props.preview.get('body');
      attachments = this.props.preview.get('attachments');
    }

    if (this.props.type === 'block') {
      return <div className="email-preview" />;
    }

    return (
      <div className={classNames('email-preview', { 'with-attachment': attachments && attachments.size > 0 })}>
        <div className="email">
          <SimpleFrame
            frameStyles={{
              width:    '100%',
              height:   '100%',
              position: 'absolute',
              padding:  '30px',
              top:      0,
              left:     0,
              zIndex:   9,
            }}
            content={code}
            isVisible
          />
        </div>
        {this.getPreviewAttachments()}
      </div>
    );
  }
}
export default PreviewEmail;
