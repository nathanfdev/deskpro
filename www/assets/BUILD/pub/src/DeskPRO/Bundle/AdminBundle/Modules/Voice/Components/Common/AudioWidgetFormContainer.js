import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import $ from 'jquery';
import Immutable from 'immutable';
import AudioWidgetForm from 'DeskPRO/Component/AudioWidget/AudioWidget';
import AddAudioAsset from 'DeskPRO/Component/AudioWidget/AddAudioAsset';
import EditAudioAsset from 'DeskPRO/Component/AudioWidget/EditAudioAsset';
import { createVoiceAsset, deleteVoiceAsset } from '../../Actions/assetActions';

@connect()
class AudioWidgetFormContainer extends React.Component {

  static propTypes = {
    value:           PropTypes.object,
    onChange:        PropTypes.func,
    dispatch:        PropTypes.func,
    hasAutoSpeech:   PropTypes.bool,
    autoSpeechLabel: PropTypes.string
  };

  onSubmit = (data) => {
    const { onChange, dispatch } = this.props;
    this.setState({
      errors: {},
      saving: true
    });


    const promise = dispatch(createVoiceAsset(data));
    promise.success((response) => {
      onChange(response.data);
      this.setState({
        saving: false
      }, () => this.widget.closeMenu());
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

  deleteAsset = () => {
    const { value, onChange, dispatch } = this.props;
    const promise = dispatch(deleteVoiceAsset(value.id));
    promise.success(() => {
      onChange(null);
    });
  };

  render() {
    const { value, hasAutoSpeech, autoSpeechLabel } = this.props;

    return (
      <AudioWidgetForm
        {...this.state}
        ref={(c) => { this.widget = c; }}
        value={value ? Immutable.fromJS(value) : null}
        addButtonComponent={AddAudioAsset}
        editButtonComponent={EditAudioAsset}
        onSubmit={this.onSubmit}
        deleteAsset={this.deleteAsset}
        hasAutoSpeech={hasAutoSpeech}
        autoSpeechLabel={autoSpeechLabel}
      />
    );
  }
}

export default AudioWidgetFormContainer;
