import React, { PropTypes } from 'react';
import PopUp from 'DeskPRO/Component/Semantic/PopUp/PopUp';
import { Field, Select, Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';
import { Fieldset, createValue } from 'react-forms';
import Isvg from 'react-inlinesvg';
import chatSvg from '../../../../../AgentBundle/Resources/img/topbar/chat.svg';
import callSvg from '../../../../../AgentBundle/Resources/img/topbar/IM.svg';

const statusChoices = [
  { value: 'online', label: 'Online', icon: chatSvg },
  { value: 'offline', label: 'Offline', icon: callSvg },
  { value: 'launch', label: 'Lunch', icon: chatSvg },
  { value: 'away', label: 'Away', icon: callSvg }
];

class VoiceProfile extends React.Component {

  static propTypes = {
    onChange:           PropTypes.func,
    onClickPreferences: PropTypes.func,
    onClickHelp:        PropTypes.func,
    onClickLogout:      PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      formData: createValue({
        value: {
          status: 'online',
          chats:  false,
          calls:  false
        },
        onChange: this.onChange
      })
    };
  }

  onChange = (formData) => {
    this.setState({ formData });
    this.props.onChange(formData.value);
  };

  renderSelectValue = option => (
    <span>
      <Isvg src={option.icon} />
      <span className="voice-profile-status-label">{option.label}</span>
    </span>
  );

  render() {
    const { formData } = this.state;
    const { onClickPreferences, onClickHelp, onClickLogout } = this.props;

    return (
      <div className="voice-profile">
        <div className="voice-header">
          Your profile
        </div>
        <div>
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
                    <Isvg src={chatSvg} />
                    <span className="voice-profile-status-checkbox-title">Chats</span>
                  </div>
                  <div className="voice-profile-status-checkbox">
                    <Field select="calls">
                      <Checkbox />
                    </Field>
                    <Isvg src={callSvg} />
                    <span className="voice-profile-status-checkbox-title">Calls</span>
                  </div>
                </div>}
            </div>
          </Fieldset>
          <div className="voice-profile-menu">
            <div className="voice-profile-menu-item" onClick={onClickPreferences}>
              <i className="fa fa-gear" />
              Preferences
            </div>
            <div className="voice-profile-menu-item" onClick={onClickHelp}>
              <i className="fa fa-question-circle" />
              Help
            </div>
            <div className="voice-profile-menu-item" onClick={onClickLogout}>
              <i className="fa fa-reply" />
              Log out
            </div>
          </div>
        </div>
      </div>
    );
  }
}

class VoiceProfilePopup extends React.Component {

  render() {
    return (
      <PopUp
        positionMy="left top"
        positionAt="left bottom"
        zIndex={99999}
        content={(<VoiceProfile {...this.props} />)}
      >
        <button>Button</button>
      </PopUp>
    );
  }
}

export default VoiceProfilePopup;
