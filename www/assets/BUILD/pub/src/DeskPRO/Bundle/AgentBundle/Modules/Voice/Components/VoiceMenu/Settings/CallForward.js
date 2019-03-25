import PropTypes from 'prop-types';
import React from 'react';
import { Fieldset } from '@deskpro/react-forms';
import { Form, Field, Toggle, PhoneInput } from 'DeskPRO/Component/Semantic/ReactForm';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import classNames from 'classnames';

class CallForward extends BaseForm {

  static propTypes = {
    me:       PropTypes.object,
    onSubmit: PropTypes.func
  };

  getDefaultState() {
    const { me } = this.props;

    return {
      agent_data: {
        agent_can_use_forwarding: me ? me.getIn(['agent_data', 'agent_can_use_forwarding']) : false,
        forwarding_number:        (me && me.getIn(['agent_data', 'forwarding_number'])) || ''
      }
    };
  }

  render() {
    const { formData, saving } = this.state;

    return (
      <div className="call-forward" ref={(c) => { this.node = c; }}>
        <Form formValue={formData}>
          <Fieldset>
            <Field select="agent_data">
              <CallForwardField />
            </Field>
            <div className="call-forward-save">
              <button className={classNames('ui button', { loading: saving })} onClick={this.onSubmit}>Save</button>
            </div>
          </Fieldset>
        </Form>
        <div className="voice-forward-help">
          Forward incoming calls to this number. Any time a call rings you in Deskpro, it will also ring this phone.
          You will be able to answer the call either in Deskpro or on your phone.
        </div>
        <div className="voice-forward-help">
          When enabled, calls will only be forwarded when an Agents&apos; status is set as &apos;Online&apos; for calls.
          This applies even if the Agent is &apos;Online&apos; but logged out of the helpdesk.
        </div>
      </div>
    );
  }
}

class CallForwardField extends React.Component {

  static propTypes = {
    value: PropTypes.object
  };

  render() {
    const { value } = this.props;

    return (
      <div>
        <Field select="agent_can_use_forwarding">
          <Toggle className="small" disabled={!value.forwarding_number}>
            Enable call forwarding
          </Toggle>
        </Field>
        <Field select="forwarding_number" label="Forwarding number">
          <PhoneInput supportSip type="text" />
        </Field>
      </div>
    );
  }
}

export default CallForward;
