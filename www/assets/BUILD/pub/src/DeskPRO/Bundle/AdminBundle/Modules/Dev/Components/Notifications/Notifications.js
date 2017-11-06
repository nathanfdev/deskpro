import React from 'react';
import { Fieldset } from '@deskpro/react-forms';
import classNames from 'classnames';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import { Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';
import SectionHeader from '../../../Common/Components/SectionHeader';
import AgentsSelectContainer from '../../../Common/Components/Select/AgentsSelectContainer';

class Notifications extends BaseForm {

  render() {
    const { formData, saving } = this.state;

    return (
      <div className="page">
        <SectionHeader title="Dev: Agent Notifications" dividing />

        <Form onSubmit={this.onSubmit} formValue={formData}>
          <Fieldset className="dev-gen-notifications-form">
            <div>
              <button className={classNames('ui button', { loading: saving })}>
                Generate Notifications
              </button>
            </div>
            <span className="for">
              for
            </span>
            <Field select="agent">
              <AgentsSelectContainer />
            </Field>
          </Fieldset>
        </Form>
      </div>
    );
  }
}

export default Notifications;
