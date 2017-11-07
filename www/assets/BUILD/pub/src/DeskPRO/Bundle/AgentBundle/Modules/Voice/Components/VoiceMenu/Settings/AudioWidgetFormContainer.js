import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import $ from 'jquery';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import AudioWidgetForm from 'DeskPRO/Component/AudioWidget/AudioWidget';
import AddAudioAsset from 'DeskPRO/Component/AudioWidget/AddAudioAsset';
import EditAudioAsset from 'DeskPRO/Component/AudioWidget/EditAudioAsset';
import { editAgentProfile } from '../../../../Agent/Actions/agentActions';
import { createVoiceAsset } from '../../../Actions/assetActions';

@connect(state => ({
  me: meSelector(state)
}))
class AudioWidgetFormContainer extends React.Component {

  static propTypes = {
    me:       PropTypes.object,
    dispatch: PropTypes.func
  };

  onSubmit = (data) => {
    const { dispatch } = this.props;

    this.setState({
      errors: {},
      saving: true
    });

    const assetPromise = dispatch(createVoiceAsset(data));
    assetPromise.success((response) => {
      const profilePromise = dispatch(editAgentProfile({
        voicemail_asset: response.data
      }));
      profilePromise.success(() => {
        this.setState({
          saving: false
        }, () => this.widget.onClose());
      });
      profilePromise.error(() => {
        this.setState({
          errors: {
            errors: [
              {
                message: 'Unable to save profile'
              }
            ]
          },
          saving: false
        });
      });
    });
    assetPromise.error((result) => {
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
    const { me } = this.props;

    return (
      <AudioWidgetForm
        {...this.state}
        ref={(c) => { this.widget = c; }}
        value={me.getIn(['agent_data', 'voicemail_asset'])}
        addButtonComponent={AddAudioAsset}
        editButtonComponent={EditAudioAsset}
        onSubmit={this.onSubmit}
      />
    );
  }
}

export default AudioWidgetFormContainer;
