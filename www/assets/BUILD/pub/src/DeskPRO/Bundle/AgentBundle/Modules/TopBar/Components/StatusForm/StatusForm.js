import React, { PropTypes } from 'react';
import { Field, Select, Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';
import { Fieldset, createValue } from 'react-forms';
import Immutable from 'immutable';
import Isvg from 'react-inlinesvg';
import '../../../../../AgentBundle/Resources/img/topbar/chat.svg';
import '../../../../../AgentBundle/Resources/img/topbar/IM.svg';

const assetPath = `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img`;
const statusChoices = [
  { value: 'idle', label: 'Online', icon: `${assetPath}/topbar/notifications.svg` },
  { value: 'offline', label: 'Offline', icon: `${assetPath}/topbar/IM.svg` }
];

class StatusForm extends React.Component {

  static propTypes = {
    me:           PropTypes.object,
    voiceEnabled: PropTypes.bool,
    onChange:     PropTypes.func
  };

  constructor(props) {
    super(props);
    const agentData = props.me.get('agent_data') || Immutable.fromJS({});

    this.state = {
      formData: createValue({
        value: {
          status: agentData.get('available_status') || 'offline',
          chats:  false,
          calls:  agentData.get('agent_calls_enabled')
        },
        onChange: this.onChange
      })
    };
  }

  onChange = (formData, changedFields) => {
    const { onChange } = this.props;
    const value = formData.value;

    if (changedFields.indexOf('status') !== -1) {
      if (value.status === 'offline') {
        value.chats = false;
        value.calls = false;
      } else {
        value.chats = true;
        value.calls = true;
      }
    }

    this.setState({ formData });
    onChange(value);
  };

  renderSelectValue = option => (
    <span>
      <Isvg src={option.icon} />
      <span className="voice-profile-status-label">{option.label}</span>
    </span>
  );

  render() {
    const { voiceEnabled } = this.props;
    const { formData } = this.state;

    return (
      <Fieldset formValue={formData}>
        <div className="voice-profile-status">
          <h2>Status</h2>
          <Field select="status">
            <Select
              choices={statusChoices}
              clearable={false}
              optionRenderer={this.renderSelectValue}
              valueRenderer={this.renderSelectValue}
            />
          </Field>
          {formData.value.status === 'offline'
            ? <div className="voice-profile-status-empty-checkboxes" />
            : <div className="voice-profile-status-checkboxes">
              <div className="voice-profile-status-checkbox">
                <Field select="chats">
                  <Checkbox />
                </Field>
                <Isvg src={`${assetPath}/topbar/chat.svg`} />
                <span className="voice-profile-status-checkbox-title">Chats</span>
              </div>
              {voiceEnabled &&
                <div className="voice-profile-status-checkbox">
                  <Field select="calls">
                    <Checkbox />
                  </Field>
                  <Isvg src={`${assetPath}/topbar/IM.svg`} />
                  <span className="voice-profile-status-checkbox-title">Calls</span>
                </div>}
            </div>}
        </div>
      </Fieldset>
    );
  }
}

export default StatusForm;
