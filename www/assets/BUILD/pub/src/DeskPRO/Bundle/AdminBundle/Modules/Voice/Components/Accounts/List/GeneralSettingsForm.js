import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Fieldset } from '@deskpro/react-forms';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import { Input, Form, Field, Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';
import classNames from 'classnames';

class GeneralSettingsForm extends BaseForm {

  static propTypes = {
    settings: PropTypes.object
  };

  getDefaultState() {
    const { settings } = this.props;

    return {
      group_missed_call_tickets:         settings ? settings.get('group_missed_call_tickets') : false,
      group_missed_call_tickets_timeout: settings ? settings.get('group_missed_call_tickets_timeout') : 0
    };
  }

  render() {
    const { formData, saving } = this.state;

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
