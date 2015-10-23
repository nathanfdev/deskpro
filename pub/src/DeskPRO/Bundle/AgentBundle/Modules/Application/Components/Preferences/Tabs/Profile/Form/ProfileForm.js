import React, { PropTypes } from 'react';
import { FieldWrapper } from './Fields/FieldWrapper';
import { Name } from './Fields/Name';
import { Email } from './Fields/Email';
import { PrimaryEmail } from './Fields/PrimaryEmail';
import { Phone } from './Fields/Phone';
import { Language } from './Fields/Language';
import { Timezone } from './Fields/Timezone';
import { Password } from './Fields/Password';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export class ProfileForm extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    languages: PropTypes.object.isRequired,
    timezones: PropTypes.object.isRequired,
    profile: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    const profile = props.profile;

    this.state = {
      name: profile.get('name'),
      overrideDefaultName: profile.get('overrideDefaultName'),
      emails: profile.get('emails') || [],
      primaryEmail: profile.get('primary_email'),
      phone: profile.get('phone_number'),
      language: profile.get('language_id'),
      timezone: profile.get('timezone') || 'UTC',
      password: null,
      confirmPassword: null
    };
  }

  onChangeName = (value) => {
    this.setState({
      name: value
    });
  };

  onToggleOverrideDefaultName = () => {
    this.setState({
      overrideDefaultName: !this.state.overrideDefaultName
    });
  };

  onChangeEmails = (emails) => {
    this.setState({
      emails: emails
    });

    if (emails.length) {
      this.setState({
        primaryEmail: emails[0]
      });
    }
  };

  onChangePrimaryEmail = (value) => {
    this.setState({
      primaryEmail: value
    });
  };

  onChangePhone = (value) => {
    this.setState({
      phone: value
    });
  };

  onChangeLanguage = (value) => {
    this.setState({
      language: value
    });
  };

  onChangeTimezone = (value) => {
    this.setState({
      timezone: value
    });
  };

  onChangePassword = (value) => {
    this.setState({
      password: value
    });
  };

  onChangeConfirmPassword = (value) => {
    this.setState({
      confirmPassword: value
    });
  };

  submitForm = (event) => {
    event.preventDefault();
    console.log(this.state);

    DpApi.sendPut('DP_API/me/profile', this.state);
  };

  renderNameField() {
    return (
      <FieldWrapper label="Your name">
        <Name value={this.state.name} onChange={this.onChangeName} />
      </FieldWrapper>
    );
  }

  renderOverrideDefaultNameField() {
    return (
      <div className="bucket short">
        <label className="simple-label">
          <input type="checkbox"
                 checked={this.state.overrideDefaultName}
                 onChange={this.onToggleOverrideDefaultName} />

          Override default name?
        </label>
        <span className="small">(will be displayed to users instead of your real name.)</span>
      </div>
    );
  }

  renderEmailField() {
    return (
      <FieldWrapper label="Your email">
        <Email emails={this.state.emails}
               onChange={this.onChangeEmails} />
      </FieldWrapper>
    );
  }

  renderPrimaryEmailField() {
    const emails = this.state.emails;

    if (emails && emails.length > 1 && emails[1]) {
      return (
        <FieldWrapper label="Primary email">
          <PrimaryEmail emails={this.state.emails}
                        value={this.state.primaryEmail}
                        onChange={this.onChangePrimaryEmail} />
        </FieldWrapper>
      );
    }

    return null;
  }

  renderPhoneField() {
    return (
      <FieldWrapper label="Phone #">
        <Phone value={this.state.phone} onChange={this.onChangePhone} />
      </FieldWrapper>
    );
  }

  renderLanguageField() {
    const { languages } = this.props;
    if (languages.size < 2) {
      return null;
    }

    return (
      <FieldWrapper label="Language">
        <Language languages={languages}
                  value={this.state.language}
                  onChange={this.onChangeLanguage} />
      </FieldWrapper>
    );
  }

  renderTimezoneField() {
    const { timezones } = this.props;

    return (
      <FieldWrapper label="Time Zone">
        <Timezone timezones={timezones}
                  value={this.state.timezone}
                  onChange={this.onChangeTimezone} />
      </FieldWrapper>
    );
  }

  renderPasswordField() {
    return (
      <FieldWrapper label="Password">
        <Password
          value={this.state.password}
          confirmValue={this.state.confirmPassword}
          onChangeValue={this.onChangePassword}
          onChangeConfirmValue={this.onChangeConfirmPassword} />
      </FieldWrapper>
    );
  }

  render() {
    return (
      <form className="popup-form-default">
        {this.renderNameField()}
        {this.renderOverrideDefaultNameField()}
        {this.renderEmailField()}
        {this.renderPrimaryEmailField()}
        {this.renderPhoneField()}

        <hr />

        {this.renderLanguageField()}
        {this.renderTimezoneField()}

        <hr />

        {this.renderPasswordField()}

        <input type="submit" onClick={this.submitForm} />
      </form>
    );
  }
}
