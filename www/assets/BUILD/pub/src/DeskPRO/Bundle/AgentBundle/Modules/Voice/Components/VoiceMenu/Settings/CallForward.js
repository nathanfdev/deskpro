import PropTypes from 'prop-types';
import React from 'react';
import { Fieldset } from '@deskpro/react-forms';
import { Form, Field, Toggle, Input } from 'DeskPRO/Component/Semantic/ReactForm';
import { PhoneInput } from 'DeskPRO/Component/Semantic/ReactForm/PhoneInput';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import classNames from 'classnames';
import $ from 'jquery';

class CallForward extends BaseForm {

  static propTypes = {
    me:       PropTypes.object,
    onSubmit: PropTypes.func
  };

  componentDidMount() {
    super.componentDidMount();

    const $checkbox = $('.toggle', this.node);
    $checkbox.on('click', () => {
      const { formData } = this.state;
      if (formData.value.agent_data.forwarding_number) {
        setTimeout(this.onSubmit, 1);
      }
    });

    const initPhoneCallbacks = () => {
      setTimeout(() => {
        const $phone = $('input[type=text]', this.node);
        $phone.on('keydown blur change', () => setTimeout(() => {
          const { formData } = this.state;
          const newVal = `${$phone.val()}`.replace(/^sip:/, '');

          if (!newVal) {
            const value = formData.value;
            value.agent_data.agent_can_use_forwarding = false;
            this.onChange(formData, ['agent_data', 'agent_can_use_forwarding']);
          }
        }, 1));
      }, 1);
    };

    const $sipCheckbox = $('.voice-sip-number-mode', this.node);
    $sipCheckbox.on('click', initPhoneCallbacks);

    initPhoneCallbacks();
  }

  onChange = (formData, changedFields) => {
    // forwarding was just enabled, enable forwarding if logged out as well
    if (changedFields.indexOf('agent_can_use_forwarding') !== -1
      && formData.value.agent_data.agent_can_use_forwarding
    ) {
      changedFields.push('forwarding_logged_out');
      formData.value.agent_data.forwarding_logged_out = true;
    }

    this.setState({ formData });
  };

  getDefaultState() {
    const { me } = this.props;
    const canUseForwarding = me ? me.getIn(['agent_data', 'agent_can_use_forwarding']) : false;
    const forwardingLoggedOut = me ? me.getIn(['agent_data', 'forwarding_logged_out']) : false;

    return {
      agent_data: {
        agent_can_use_forwarding: canUseForwarding,
        forwarding_number:        (me && me.getIn(['agent_data', 'forwarding_number'])) || '',
        forwarding_ring_timeout:  (me && me.getIn(['agent_data', 'forwarding_ring_timeout'])) || 10,
        forwarding_logged_out:    canUseForwarding && forwardingLoggedOut,
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
          Forward incoming calls to a different number. Incoming calls can be answered
          in Deskpro or by answering this phone number.
        </div>
        <div className="voice-forward-help">
          <h3>Personal Voicemail</h3>
          Voicemail on your number may conflict with regular handling and queuing of
          calls in Deskpro. The system cannot know if _you_ answered the call, or
          if your _voicemail_ answered the call. If the user is sent to your voicemail,
          then the user will not be sent through to the next agent online because the
          call will be considered answered.
        </div>
        <div className="voice-forward-help">
          Here are some steps you can take to avoid these issues:
          <ul>
            <li>Ensure the maximum ring time entered above is LESS THAN your voicemail time.</li>
            <li>On some devices, explicitly declining a call may send the user directly to voicemail immediately. You should avoid declining calls on such devices.</li>
            <li>Calls are forwarded from your Voice phone numbers in Deskpro. Some devices/providers may allow you to disable voicemail for these specific numbers.</li>
          </ul>
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
        <Field select="forwarding_logged_out">
          <Toggle className="small" disabled={!value.agent_can_use_forwarding}>
            Only forward calls when I am not logged-in to the helpdesk
          </Toggle>
        </Field>
        <Field select="forwarding_number" label="Forwarding number">
          <PhoneInput supportSip type="text" />
        </Field>
        <Field select="forwarding_ring_timeout">
          <RingTimeout />
        </Field>
      </div>
    );
  }
}

class RingTimeout extends React.Component {

  static propTypes = {
    value:    PropTypes.object,
    onChange: PropTypes.func
  };

  render() {
    const { value, onChange } = this.props;

    return (
      <div className="ring-timeout">
        <span>Ring for a maximum of</span>
        <span><Input type="number" value={value} onChange={onChange} /></span>
        <span>seconds</span>
      </div>
    );
  }
}

export default CallForward;
