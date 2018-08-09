import PropTypes from 'prop-types';
import React from 'react';
import { filenameMaxLength } from 'DeskPRO/Component/Util/Filename';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class UploadingFile extends React.Component {

  static propTypes = {
    file:     PropTypes.object,
    isFailed: PropTypes.bool,
    onRemove: PropTypes.func
  };

  onRemove = (event) => {
    event.preventDefault();

    const { file, onRemove } = this.props;
    onRemove(file);
  };

  render() {
    const { file, isFailed } = this.props;

    return (
      <div className="dpdesignportal-chat-form-attached-file">
        <div className="dpdesignportal-chat-form-attached-file-icon">
          <i className="far fa-file-pdf" />
        </div>
        <div className="attached-file-title">
          {filenameMaxLength(file.name, isFailed ? 20 : 30)}
          {isFailed && <span className="failed-status">({portalPhrases.get('portal.chat.asset_failed')})</span>}
          {isFailed
            ?
              <div>
                <a className="dpdesignportal-chat-form-attached-file-remove" onClick={this.onRemove}>
                  <i className="fa fa-times-circle" />
                </a>
              </div>
            : <div className="spinner dpdesignportal-chat-form-attached-file-spinner"><i /></div>
          }
        </div>
      </div>
    );
  }
}
