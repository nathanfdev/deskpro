import PropTypes from 'prop-types';
import React from 'react';
import { Fieldset } from '@deskpro/react-forms';
import classNames from 'classnames';
import Modal from 'DeskPRO/Component/Semantic/Modal';
import { Button } from '@deskpro/react-components';
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

  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      confirmDeletion: false
    };
  }

  onCancel = (event) => {
    event.preventDefault();
    this.props.onReturnBack();
  };

  performDelete = () => {
    event.preventDefault();
    this.props.onDelete();
    this.setState({
      confirmDeletion: false
    });
  };

  showDeleteConfirmation = () => {
    this.setState({
      confirmDeletion: true
    });
  };

  rejectDelete = () => {
    this.setState({
      confirmDeletion: false
    });
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
    const { saving, confirmDeletion } = this.state;

    return (
      <div className="page">
        <Modal
          isOpen={confirmDeletion}
          title="Confirm deletion"
          contentStyles={{ top: '25%', left: '37%', bottom: 'auto', height: '150px', width: '30%' }}
        >
          <h2>Do you really want to delete this Auto Attendant? This cannot be undone.</h2>
          <div>
            <span style={{ float: 'left' }}>
              <Button size="large" type="secondary" onClick={this.rejectDelete}>Decline</Button>
            </span>
            <span style={{ float: 'right' }}>
              <Button size="large" type="cta" onClick={this.performDelete}>Confirm</Button>
            </span>
          </div>
        </Modal>
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
              <span className="voice-delete-button" onClick={this.showDeleteConfirmation}>
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
