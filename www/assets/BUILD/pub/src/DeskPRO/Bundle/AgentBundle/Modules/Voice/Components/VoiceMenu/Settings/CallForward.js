import PropTypes from 'prop-types';
import React from 'react';
import { Fieldset } from '@deskpro/react-forms';
import { Form, Field, Toggle, PhoneInput } from 'DeskPRO/Component/Semantic/ReactForm';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import $ from 'jquery';

class CallForward extends BaseForm {

  static propTypes = {
    me:       PropTypes.object,
    onSubmit: PropTypes.func
  };

  componentDidMount() {
    super.componentDidMount();

    const $checkbox = $('.toggle', this.node);
    const $phone = $('input[type=text]', this.node);

    $checkbox.on('click', this.onSubmit);
    $phone.on('blur', this.onSubmit);
    $phone.on('keydown', (event) => {
      const code = event.keyCode || event.which;

      if (code === 13) {
        event.preventDefault();
        this.onSubmit();
      }
    });
  }

  getDefaultState() {
    const { me } = this.props;

    return {
      agent_data: {
        agent_can_use_forwarding: me ? me.getIn(['agent_data', 'agent_can_use_forwarding']) : false,
        forwarding_number:        me ? me.getIn(['agent_data', 'forwarding_number']) : ''
      }
    };
  }

  render() {
    const { formData } = this.state;

    return (
      <div className="call-forward" ref={(c) => { this.node = c; }}>
        <Form formValue={formData}>
          <Fieldset>
            <Field select="agent_data">
              <CallForwardField />
            </Field>
          </Fieldset>
        </Form>
        <div className="voice-forward-help">
          Forward incoming calls to this number. Any time a call rings you in Deskpro, it will also ring this phone.
          You will be able to answer the call either in Deskpro or on your phone.
        </div>
      </div>
    );
  }
}

class CallForwardField extends React.Component {

  render() {
    return (
      <div>
        <Field select="agent_can_use_forwarding">
          <Toggle className="small">
            Enable call forwarding
          </Toggle>
        </Field>
        <Field select="forwarding_number" label="Forwarding number">
          <PhoneInput type="text" />
        </Field>
      </div>
    );
  }
}

export default CallForward;
