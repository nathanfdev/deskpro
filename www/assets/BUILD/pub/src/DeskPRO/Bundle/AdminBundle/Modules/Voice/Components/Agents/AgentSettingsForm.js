import React, { PropTypes } from 'react';
import { Fieldset } from 'react-forms';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import { BlurInput, Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';

class AgentSettingsForm extends BaseForm {

  static propTypes = {
    settings: PropTypes.object
  };

  getDefaultState() {
    const { settings } = this.props;

    return {
      agent_voicemail_timeout: settings.get('agent_voicemail_timeout') || 30
    };
  }

  render() {
    const { formData } = this.state;

    return (
      <div className="agent-settings-form">
        <Form onSubmit={this.onSubmit} formValue={formData}>
          <Fieldset>
            <Field select="agent_voicemail_timeout" label="Agent Voicemail Timeout">
              <BlurInput select="agent_voicemail_timeout" type="number" />
            </Field>
          </Fieldset>
        </Form>
      </div>
    );
  }
}

export default AgentSettingsForm;
