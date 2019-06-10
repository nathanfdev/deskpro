import PropTypes from 'prop-types';
import React from 'react';
import Isvg from 'react-inlinesvg';
import '../../Bundle/AppBundle/Resources/img/audio_widget/delete.svg';

class EditAudioAsset extends React.Component {

  static propTypes = {
    value:       PropTypes.object,
    onOpen:      PropTypes.func,
    deleteAsset: PropTypes.func
  };

  openAsset = (event) => {
    event.preventDefault();
    this.props.onOpen();
  };

  deleteAsset = (event) => {
    event.preventDefault();
    this.props.deleteAsset();
  };

  renderButtons() {
    return (
      <span>
        <span onClick={this.openAsset}>
          <Isvg
            className="asset-edit-icon"
            src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AppBundle/Resources/img/audio_widget/edit.svg`}
          />
        </span>
        <span onClick={this.deleteAsset}>
          <Isvg
            className="asset-delete-icon"
            src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AppBundle/Resources/img/audio_widget/delete.svg`}
          />
        </span>
      </span>
    );
  }

  renderText() {
    const { value } = this.props;

    return (
      <div className="edit-audio-asset">
        <Isvg
          className="asset-type-icon"
          src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AppBundle/Resources/img/audio_widget/text-audio.svg`}
        />
        <span className="asset-text">{value.get('auto_generated') ? 'Auto generated' : value.get('text')}</span>
        {this.renderButtons()}
      </div>
    );
  }

  renderUpload() {
    const { value } = this.props;

    return (
      <div className="edit-audio-asset">
        <Isvg
          className="asset-type-icon"
          src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AppBundle/Resources/img/audio_widget/upload.svg`}
        />
        <span className="asset-text">{value.getIn(['blob', 'filename'])}</span>
        {this.renderButtons()}
      </div>
    );
  }

  renderRecord() {
    const { value } = this.props;

    return (
      <div className="edit-audio-asset">
        <Isvg
          className="asset-type-icon"
          src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AppBundle/Resources/img/audio_widget/record.svg`}
        />
        <span className="asset-text">{value.get('name')}</span>
        {this.renderButtons()}
      </div>
    );
  }

  render() {
    const { value } = this.props;
    const type = value.get('type');

    if (type === 'text') {
      return this.renderText();
    } else if (type === 'upload') {
      return this.renderUpload();
    } else if (type === 'record') {
      return this.renderRecord();
    }

    return null;
  }
}

export default EditAudioAsset;
