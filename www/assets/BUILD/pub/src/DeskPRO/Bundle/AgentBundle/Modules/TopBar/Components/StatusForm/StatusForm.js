import PropTypes from 'prop-types';
import React from 'react';
import { Field, Select, Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';
import { Fieldset, createValue } from '@deskpro/react-forms';
import Immutable from 'immutable';
import Isvg from 'react-inlinesvg';
import classNames from 'classnames';
import '../../../../../AgentBundle/Resources/img/topbar/chat.svg';
import '../../../../../AgentBundle/Resources/img/topbar/IM.svg';

const assetPath = `${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img`;
const statusChoices = [
  { value: 'idle', label: 'Online', icon: `${assetPath}/topbar/notifications.svg` },
  { value: 'offline', label: 'Offline', icon: `${assetPath}/topbar/IM.svg` }
];

class StatusForm extends React.Component {

  static propTypes = {
    voiceAvailable:  PropTypes.bool,
    voiceEnabled:    PropTypes.bool,
    userChatEnabled: PropTypes.bool,
    onChange:        PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      formData: this.getDefaultValue(props)
    };
  }

  componentWillReceiveProps(newProps) {
    this.setState({
      formData: this.getDefaultValue(newProps)
    });
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

  getDefaultValue(props) {
    const agentData = props.me.get('agent_data') || Immutable.fromJS({});

    return createValue({
      value: {
        status: agentData.get('available_status') || 'offline',
        chats:  props.userChatEnabled,
        calls:  agentData.get('agent_calls_enabled')
      },
      onChange: this.onChange
    });
  }

  renderSelectValue = option => (
    <span>
      <Isvg src={option.icon} />
      <span className="voice-profile-status-label">{option.label}</span>
    </span>
  );

  render() {
    const { voiceAvailable, voiceEnabled, userChatEnabled } = this.props;
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
          <div className="voice-profile-status-checkboxes">
            {window.DESKPRO_APP_SETTINGS['core.apps_chat'] && window.DESKPRO_PERSON_PERMS['agent_chat.use'] &&
              <div className="voice-profile-status-checkbox">
                <Field select="chats">
                  <Checkbox />
                </Field>
                <Isvg src={`${assetPath}/topbar/chat.svg`} className={classNames({ on: userChatEnabled })} />
                <span className="voice-profile-status-checkbox-title">Chats</span>
              </div>}
            {voiceAvailable &&
              <div className="voice-profile-status-checkbox">
                <Field select="calls">
                  <Checkbox />
                </Field>
                <i className={classNames('ui call icon', formData.value.calls ? 'green' : 'disabled')} />
                <span className="voice-profile-status-checkbox-title">
                  Calls
                  {!voiceEnabled &&
                    <span className="voice-profile-status-checkbox-title-disabled">
                      (Use HTTPS for calls)
                    </span>
                  }
                </span>
              </div>}
          </div>
        </div>
      </Fieldset>
    );
  }
}

export default StatusForm;
