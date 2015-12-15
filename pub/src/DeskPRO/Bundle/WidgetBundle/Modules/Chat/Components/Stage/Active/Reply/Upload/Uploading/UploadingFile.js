import React, { PropTypes } from 'react';
import { filenameMaxLength } from 'DeskPRO/Component/Util/Filename';

export class UploadingFile extends React.Component {

  static propTypes = {
    file: PropTypes.object,
    failed: PropTypes.bool,
    onRepeat: PropTypes.func,
    onRemove: PropTypes.func
  };

  onRepeat = event => {
    event.preventDefault();

    const { file, onRepeat } = this.props;
    onRepeat(file);
  };

  onRemove = event => {
    event.preventDefault();

    const { file, onRemove } = this.props;
    onRemove(file);
  };

  render() {
    const { file, failed } = this.props;

    return (
      <div className="dpdesignportal-chat-form-attached-file">
        <div className="dpdesignportal-chat-form-attached-file-icon">
          <i className="fa fa-file-pdf-o"></i>
        </div>
        <div className="attached-file-title">
          {filenameMaxLength(file.name, failed ? 20 : 30)}
          {failed && <span className="failed-status">(failed)</span>}
          {failed
            ? <div>
                <a className="dpdesignportal-chat-form-attached-file-repeat" onClick={this.onRepeat}>
                  <i className="fa fa-repeat"/>
                </a>
                <a className="dpdesignportal-chat-form-attached-file-remove" onClick={this.onRemove}>
                  <i className="fa fa-times-circle"/>
                </a>
              </div>
            : <div className="spinner dpdesignportal-chat-form-attached-file-spinner">
                <i/>
              </div>
          }
        </div>
      </div>
    );
  }
}
