import React, { PropTypes } from 'react';
import { Fieldset } from 'react-forms';
import classNames from 'classnames';
import { Input, Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import BackButton from '../../../../Common/Components/BackButton';
import DialNumberCheckbox from './DialNumber/DialNumberCheckbox';
import DialNumbers from './DialNumber/DialNumbers';
import AudioWidgetFormContainer from '../../Common/AudioWidgetFormContainer';

class AutoAttendantForm extends BaseForm {

  static propTypes = {
    autoAttendant: PropTypes.object,
    onReturnBack:  PropTypes.func,
    onSubmit:      PropTypes.func,
    onDelete:      PropTypes.func
  };

  onCancel = (event) => {
    event.preventDefault();
    this.props.onReturnBack();
  };

  onDelete = (event) => {
    event.preventDefault();
    this.props.onDelete();
  };

  getDefaultState() {
    const autoAttendant = this.props.autoAttendant;
    const audioAsset    = autoAttendant && autoAttendant.get('audio_asset');

    return {
      name:              autoAttendant ? autoAttendant.get('name') : '',
      audio_asset:       audioAsset ? audioAsset.toJS() : null,
      targets:           autoAttendant ? autoAttendant.get('targets').toJS() : {},
      allow_repeat_menu: autoAttendant ? autoAttendant.get('allow_repeat_menu') : true,
      allow_extension:   autoAttendant ? autoAttendant.get('allow_extension') : true
    };
  }

  render() {
    const { autoAttendant, onReturnBack } = this.props;
    const { saving } = this.state;

    return (
      <div className="page">
        <BackButton onClick={onReturnBack} />
        <SectionHeader title={autoAttendant ? 'Update Auto Attendant' : 'Create new Auto Attendant'} dividing />

        <div className="voice-auto-attendant-form">
          <Form onSubmit={this.onSubmit} formValue={this.state.formData}>
            <Fieldset>
              <Field select="name" label="Auto Attendant name">
                <Input type="text" />
              </Field>
              <Field select="audio_asset" className="audio-asset" label="Audio">
                <AudioWidgetFormContainer
                  hasAutoSpeech
                  autoSpeechLabel="Auto generate speech for choices in the form 'For X press 1'"
                />
              </Field>
              <Field select="targets" label="Dialpad inputs and targets">
                <DialNumbers autoAttendant={autoAttendant} />
              </Field>

              <div className="dial-number-checkbox-group">
                <Field select="allow_repeat_menu">
                  <DialNumberCheckbox
                    dialNumber="*"
                    label="Allow users to press the ‘*’ to repeat the menu"
                  />
                </Field>
                <Field select="allow_extension">
                  <DialNumberCheckbox
                    dialNumber="#"
                    label="Allow users to press the ‘#’ key to enter an extension number"
                  />
                </Field>
              </div>

              <button className={classNames('ui button', { loading: saving })}>
                {autoAttendant ? 'Update' : 'Create'}
              </button>
              <button
                className={classNames('ui basic button cancel-button', { disabled: saving })}
                onClick={this.onCancel}
              >
                Cancel
              </button>

              {autoAttendant &&
              <span className="voice-delete-button" onClick={this.onDelete}>
                Delete this Auto Attendant
              </span>}
            </Fieldset>
          </Form>
        </div>
      </div>
    );
  }
}

export default AutoAttendantForm;
