import PropTypes from 'prop-types';
import React from 'react';
import { Fieldset } from '@deskpro/react-forms';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import { Input, Form, Field, RecordsChoiceWrapper, Select } from 'DeskPRO/Component/Semantic/ReactForm';
import classNames from 'classnames';

class AgentSettingsForm extends BaseForm {

  static propTypes = {
    settings:          PropTypes.object,
    ticketDepartments: PropTypes.object,
  };

  getDefaultState() {
    const { settings } = this.props;

    return {
      agent_voicemail_timeout:  settings.get('agent_voicemail_timeout') || 15,
      agent_default_department: settings.get('agent_default_department')
    };
  }

  render() {
    const { ticketDepartments } = this.props;
    const { formData, saving } = this.state;

    return (
      <div className="agent-settings-form">
        <Form onSubmit={this.onSubmit} formValue={formData}>
          <Fieldset>
            <Field select="agent_voicemail_timeout" label="Agent Voicemail Timeout (in Seconds)">
              <Input select="agent_voicemail_timeout" type="number" />
            </Field>
            <Field
              select="agent_default_department"
              label="Default department for agent calls"
              help="This department will be set on voice tickets that get sent directly to an agent (i.e. rather than through a queue)."
            >
              <RecordsChoiceWrapper records={ticketDepartments} labelProp="title">
                <Select {...this.props} clearable={false} />
              </RecordsChoiceWrapper>
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
