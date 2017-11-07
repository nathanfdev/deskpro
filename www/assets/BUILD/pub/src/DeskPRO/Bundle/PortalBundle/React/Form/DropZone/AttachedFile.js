import PropTypes from 'prop-types';
import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { openFullImage } from 'DeskPRO/Component/Util/FullImage';

export class AttachedFile extends React.Component {

  static propTypes = {
    inputName: PropTypes.string,
    file:      PropTypes.object.isRequired,
    onDelete:  PropTypes.func.isRequired
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
    const { file, inputName } = this.props;
    const blob = file.info;
    const errors = file.errors;
    const formName = `${inputName}[${blob.id}][blob_auth]`;

    if (errors) {
      return <div dangerouslySetInnerHTML={{ __html: errors }}></div>;
    }

    return (
      <li>
        <span dangerouslySetInnerHTML={{ __html: blob.icon_html }} />
        <a href={blob.url} target="_blank" onClick={this.onViewFile}>{blob.filename}</a>
        <input type="hidden" name={formName} value={blob.authcode} />
        <span className="file-size">({blob.size})</span>
        <a href="#" className="remove-attachement" onClick={this.onDelete}>
          <i className="fa fa-times" />{portalPhrases.get('portal.general.delete')}
        </a>

        {blob.is_image && <img role="presentation" src={blob.url} ref="image" style={{ display: 'none' }} />}
      </li>
    );
  }
}
