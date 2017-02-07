import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import $ from 'jquery';
import Immutable from 'immutable';
import AudioWidgetForm from '../../Common/AudioWidget/AudioWidget';
import { createAsset, updateAsset } from '../../../Actions/assetActions';

class AddAudioAsset extends React.Component {

  static propTypes = {
    onOpen: PropTypes.func
  };

  onClick = (event) => {
    event.preventDefault();
    this.props.onOpen();
  };

  render() {
    return (
      <div>
        <button className="ui basic button" onClick={this.onClick}>
          Choose audio source
        </button>
      </div>
    );
  }
}

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
        <b>Text to audio:</b> {value.get('text')}
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

@connect()
class AudioWidgetFormContainer extends React.Component {

  static propTypes = {
    value:    PropTypes.object,
    onChange: PropTypes.func,
    dispatch: PropTypes.func
  };

  onSubmit = (data) => {
    const { value, onChange, dispatch } = this.props;
    const assetId = value && value.id;

    this.setState({
      errors: {},
      saving: true
    });

    let promise;
    if (assetId) {
      promise = dispatch(updateAsset(assetId, data));
    } else {
      promise = dispatch(createAsset(data));
    }

    promise.success((result) => {
      if (assetId) {
        onChange({ ...data, id: assetId });
      } else {
        onChange({ ...data, id: result.data.id });
      }

      this.setState({
        saving: false
      }, () => this.widget.onClose());
    });
    promise.error((result) => {
      const resultErrors = result.errors;
      const errors = $.extend(true, resultErrors, {
        fields: {
          blob: {
            fields: {
              [data.type]: resultErrors
            }
          }
        }
      });

      this.setState({
        errors,
        saving: false
      });
    });
  };

  render() {
    const { value } = this.props;

    return (
      <AudioWidgetForm
        {...this.state}
        ref={(c) => { this.widget = c; }}
        value={value ? Immutable.fromJS(value) : null}
        addButtonComponent={AddAudioAsset}
        editButtonComponent={EditAudioAsset}
        onSubmit={this.onSubmit}
      />
    );
  }
}

export default AudioWidgetFormContainer;
