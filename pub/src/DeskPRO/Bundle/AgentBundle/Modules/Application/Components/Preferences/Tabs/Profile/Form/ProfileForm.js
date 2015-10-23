import React, { PropTypes } from 'react';
import { FieldWrapper } from './Fields/FieldWrapper';
import { Name } from './Fields/Name';
import { DisplayName } from './Fields/DisplayName';
import { Email } from './Fields/Email';
import { PrimaryEmail } from './Fields/PrimaryEmail';
import { Phone } from './Fields/Phone';
import { Language } from './Fields/Language';
import { Timezone } from './Fields/Timezone';
import { Password } from './Fields/Password';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import * as AppActions from '../../../../../Actions/AppActions';

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
      display_name: profile.get('display_name'),
      emails: profile.get('emails').toArray() || [],
      primary_email: profile.get('primary_email'),
      phone_number: profile.get('phone_number'),
      language_id: profile.get('language_id'),
      timezone: profile.get('timezone') || 'UTC',
      password: {
        first: null,
        second: null
      }
    };
  }

  onChangeName = (value) => {
    this.setState({
      name: value
    });
  };

  onChangeDisplayName = (value) => {
    this.setState({
      display_name: value
    });
  };

  onChangeEmails = (emails) => {
    this.setState({
      emails: emails
    });

    if (emails.length) {
      this.setState({
        primary_email: emails[0]
      });
    }
  };

  onChangePrimaryEmail = (value) => {
    this.setState({
      primary_email: value
    });
  };

  onChangePhoneNumber = (value) => {
    this.setState({
      phone_number: value
    });
  };

  onChangeLanguage = (value) => {
    this.setState({
      language_id: value
    });
  };

  onChangeTimezone = (value) => {
    this.setState({
      timezone: value
    });
  };

  onChangePassword = (value) => {
    this.setState({
      password: {
        first: value,
        second: this.state.password.second
      }
    });
  };

  onChangeConfirmPassword = (value) => {
    this.setState({
      password: {
        first: this.state.password.first,
        second: value
      }
    });
  };

  submitForm = (event) => {
    event.preventDefault();
    console.log(this.state);

    DpApi.sendPut('DP_API/me/profile', this.state)
      .success(() => this.props.dispatch(AppActions.closePreferences()));
  };

  renderNameField() {
    return (
      <FieldWrapper label="Your name">
        <Name value={this.state.name}
              onChange={this.onChangeName} />
      </FieldWrapper>
    );
  }

  renderDisplayNameField() {
    return (
      <DisplayName value={this.state.display_name}
                   onChange={this.onChangeDisplayName} />
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
                        value={this.state.primary_email}
                        onChange={this.onChangePrimaryEmail} />
        </FieldWrapper>
      );
    }

    return null;
  }

  renderPhoneField() {
    return (
      <FieldWrapper label="Phone #">
        <Phone value={this.state.phone_number} onChange={this.onChangePhoneNumber} />
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
                  value={this.state.language_id}
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
          value={this.state.password.first}
          confirmValue={this.state.password.second}
          onChangeValue={this.onChangePassword}
          onChangeConfirmValue={this.onChangeConfirmPassword} />
      </FieldWrapper>
    );
  }

  render() {
    return (
      <form className="popup-form-default">
        {this.renderNameField()}
        {this.renderDisplayNameField()}
        {this.renderEmailField()}
        {this.renderPrimaryEmailField()}
        {this.renderPhoneField()}

        <hr />

        {this.renderLanguageField()}
        {this.renderTimezoneField()}

        <hr />

        {this.renderPasswordField()}

        <div className="bucket">
          <div className="bucket-column-last">
            <input type="submit" onClick={this.submitForm} />
          </div>
        </div>
      </form>
    );
  }
}
