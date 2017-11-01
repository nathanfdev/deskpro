import PropTypes from 'prop-types';
import React from 'react';

class EditAudioAsset extends React.Component {

  static propTypes = {
    value:  PropTypes.object,
    onOpen: PropTypes.func
  };

  onClick = (event) => {
    event.preventDefault();
    this.props.onOpen();
  };

  renderText() {
    const { value } = this.props;

    return (
      <div>
        <b>Text to audio:</b> {value.get('auto_generated') ? 'Auto generated' : value.get('text')}
        <i className="write icon" onClick={this.onClick} />
      </div>
    );
  }

  renderUpload() {
    const { value } = this.props;

    return (
      <div>
        <b>Uploaded file:</b> {value.getIn(['blob', 'filename'])}
        <i className="write icon" onClick={this.onClick} />
      </div>
    );
  }

  renderRecord() {
    const { value } = this.props;

    return (
      <div>
        <b>Record:</b> {value.get('name')}
        <i className="write icon" onClick={this.onClick} />
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
