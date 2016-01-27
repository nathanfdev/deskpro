import React, { PropTypes } from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { openFullImage } from 'DeskPRO/Component/Util/FullImage';

export class AttachedFile extends React.Component {

  static propTypes = {
    file: PropTypes.object.isRequired,
    onDelete: PropTypes.func.isRequired
  };

  onDelete = event => {
    event.preventDefault();

    const { onDelete, file } = this.props;
    onDelete(file);
  };

  onViewFile = event => {
    event.preventDefault();

    const { file } = this.props;
    const blob = file.info;

    if (blob.is_image) {
      openFullImage(this.refs.image);
    } else {
      window.open(blob.url);
    }
  };

  render() {
    const { file } = this.props;
    const blob = file.info;
    const formName = `ticket[attachments][${blob.id}][blob_auth]`;

    return (
      <li>
        <span dangerouslySetInnerHTML={{__html: blob.icon_html }} />
        <a href={blob.url} target="_blank" onClick={this.onViewFile}>{file.file.name}</a>
        <input type="hidden" name={formName} value={blob.authcode} />
        <span className="file-size">({blob.size})</span>
        <a href="#" className="remove-attachement" onClick={this.onDelete}>
          <i className="fa fa-times" />{portalPhrases.get('portal.general.delete')}
        </a>

        {blob.is_image && <img src={blob.url} ref="image" style={{display: 'none'}} />}
      </li>
    );
  }
}
