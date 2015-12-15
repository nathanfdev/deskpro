import React, { PropTypes } from 'react';
import { filenameMaxLength } from 'DeskPRO/Component/Util/Filename';

export class UploadingFile extends React.Component {

  static propTypes = {
    file: PropTypes.object
  };

  render() {
    const { file } = this.props;

    return (
      <div className="dpdesignportal-chat-form-attached-file">
        <div className="dpdesignportal-chat-form-attached-file-icon">
          <i className="fa fa-file-pdf-o"></i>
        </div>
        <div className="attached-file-title">
          {filenameMaxLength(file.name, file.failed ? 20 : 30)}
          {file.failed && <span className="failed-status">(uploading failed)</span>}
          {file.failed
            ? <a className="dpdesignportal-chat-form-attached-file-remove">
                <i className="fa fa-repeat"/>
              </a>
            : <div className="spinner"><i/></div>
          }
        </div>
      </div>
    );
  }
}
