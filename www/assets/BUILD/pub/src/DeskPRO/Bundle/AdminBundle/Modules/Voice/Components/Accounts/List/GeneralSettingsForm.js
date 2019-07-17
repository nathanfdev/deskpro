import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Fieldset } from '@deskpro/react-forms';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import { Input, Form, Field, Checkbox, Select, NumberSelect } from 'DeskPRO/Component/Semantic/ReactForm';
import classNames from 'classnames';

class GeneralSettingsForm extends BaseForm {

  static propTypes = {
    settings: PropTypes.object,
    numbers:  PropTypes.object,
  };

  getDefaultState() {
    const { settings } = this.props;

    return {
      group_missed_call_tickets:         settings ? settings.get('group_missed_call_tickets') : false,
      group_missed_call_tickets_timeout: settings ? settings.get('group_missed_call_tickets_timeout') : 0,
      transcribe_voicemail:              settings ? settings.get('transcribe_voicemail') : false,
      forwarding_machine_detection:      settings ? settings.get('forwarding_machine_detection') : false,
      email_attach_recording:            settings ? settings.get('email_attach_recording') : false,
      email_attach_transcription:        settings ? settings.get('email_attach_transcription') : false,
      forwarding_number_type:            settings && settings.get('forwarding_number_type') || 'default',
      forwarding_number:                 settings ? settings.get('forwarding_number') : null
    };
  }

  render() {
    const { numbers } = this.props;
    const { formData, saving } = this.state;
    const forwardingNumberTypeOptions = [
      { label: 'The number the user called', value: 'default' },
      { label: 'Specify a specific number from list of all numbers.', value: 'specific' }
    ];

    return (
      <Form onSubmit={this.onSubmit} formValue={formData}>
        <Fieldset>
          <Field select="group_missed_call_tickets">
            <Checkbox label="Group missed calls and voicemails in the same ticket" />
          </Field>
          {formData.value.group_missed_call_tickets &&
          <Field select="group_missed_call_tickets_timeout">
            <GroupMissedCallTicketsTimeout />
          </Field>}
          <Field select="forwarding_machine_detection">
            <Checkbox label="Forwarding Machine Detection (+ve is stops VM pickup, -ve is latency + cost)" />
          </Field>
          <Field select="email_attach_recording">
            <Checkbox label="Attach voicemail audio to agent notification emails." />
          </Field>
          <Field select="transcribe_voicemail">
            <Checkbox label="Transcribe voicemail messages. This will insert a text version of the voicemail message into the first message of a ticket." />
          </Field>
          {formData.value.transcribe_voicemail &&
          <div className="voice-transcription-settings">
            <Field select="email_attach_transcription">
              <Checkbox label="Attach the transcript to the agent notification email." />
            </Field>
          </div>}
          <Field select="forwarding_number_type" label="Number to call from when forward calls to agents">
            <Select choices={forwardingNumberTypeOptions} />
          </Field>
          {numbers && numbers.size > 1 && formData.value.forwarding_number_type === 'specific' &&
          <Field select="forwarding_number">
            <NumberSelect numbers={numbers} placeholder="Select specific number" />
          </Field>}

          <button className={classNames('ui button', { loading: saving })}>
            Save
          </button>
        </Fieldset>
      </Form>
    );
  }
}

class GroupMissedCallTicketsTimeout extends Component {

  render() {
    return (
      <div className="missed-call-group-timeout">
        <span>when they happen within </span>
        <Input type="number" {...this.props} />
        <span>hours</span>
      </div>
    );
  }
}

export default GeneralSettingsForm;
