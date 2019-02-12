import PropTypes from 'prop-types';
import React from 'react';
import { Fieldset } from '@deskpro/react-forms';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import { Input, Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';
import classNames from 'classnames';

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
    const { formData, saving } = this.state;

    return (
      <div className="agent-settings-form">
        <Form onSubmit={this.onSubmit} formValue={formData}>
          <Fieldset>
            <Field select="agent_voicemail_timeout" label="Agent Voicemail Timeout (in Seconds)">
              <Input select="agent_voicemail_timeout" type="number" />
            </Field>

            <button className={classNames('ui button', { loading: saving })}>
              Save
            </button>
          </Fieldset>
        </Form>
      </div>
    );
  }
}

export default AgentSettingsForm;
