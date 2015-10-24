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
import * as ProfilesActions from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/profilesActions';

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
      data: {
        name: profile.get('name'),
        display_name: profile.get('display_name'),
        emails: profile.get('emails').toArray() || [],
        primary_email: profile.get('primary_email'),
        phone: {
          number: profile.get('phone').get('number'),
          extension: profile.get('phone').get('extension')
        },
        language_id: profile.get('language_id'),
        timezone: profile.get('timezone') || 'UTC',
        password: {
          first: null,
          second: null
        }
      },
      submit: false
    };
  }

  onChangeName = (value) => {
    this.updateData({
      name: value
    });
  };

  onChangeDisplayName = (value) => {
    this.updateData({
      display_name: value
    });
  };

  onChangeEmails = (emails) => {
    const diff = {
      emails: emails
    };
    if (emails.length) {
      diff.primary_email = emails[0];
    }

    this.updateData(diff);
  };

  onChangePrimaryEmail = (value) => {
    this.updateData({
      primary_email: value
    });
  };

  onChangePhone = (number, extension) => {
    this.updateData({
      phone: {
        number: number,
        extension: extension
      }
    });
  };

  onChangeLanguage = (value) => {
    this.updateData({
      language_id: value
    });
  };

  onChangeTimezone = (value) => {
    this.updateData({
      timezone: value
    });
  };

  onChangePassword = (first, second) => {
    this.updateData({
      password: {
        first: first,
        second: second
      }
    });
  };

  updateData(diff) {
    const oldData = this.state.data;
    this.setState({
      data: {...oldData, ...diff}
    });
  }

  submitForm = (event) => {
    event.preventDefault();
    const { dispatch } = this.props;

    console.log(this.state);
    const changeSubmitStatus = isSubmit => this.setState({
      submit: isSubmit
    });

    changeSubmitStatus(true);

    DpApi.sendPut('DP_API/me/profile', this.state.data)
      .success(response => {
        const records = {};
        records[response.data.id] = response.data;

        dispatch(ProfilesActions.releaseProfiles('my'));
        dispatch(ProfilesActions.setProfilesRequest('my', records, [response.data.id]));

        changeSubmitStatus(false);
      })
      .catch(http => {
        console.log(http);
        changeSubmitStatus(false);
      })
    ;
  };

  renderNameField() {
    return (
      <FieldWrapper label="Your name">
        <Name value={this.state.data.name}
              onChange={this.onChangeName} />
      </FieldWrapper>
    );
  }

  renderDisplayNameField() {
    return (
      <DisplayName value={this.state.data.display_name}
                   onChange={this.onChangeDisplayName} />
    );
  }

  renderEmailField() {
    return (
      <FieldWrapper label="Your email">
        <Email emails={this.state.data.emails}
               onChange={this.onChangeEmails} />
      </FieldWrapper>
    );
  }

  renderPrimaryEmailField() {
    const emails = this.state.data.emails;

    if (emails && emails.length > 1 && emails[1]) {
      return (
        <FieldWrapper label="Primary email">
          <PrimaryEmail emails={emails}
                        value={this.state.data.primary_email}
                        onChange={this.onChangePrimaryEmail} />
        </FieldWrapper>
      );
    }

    return null;
  }

  renderPhoneField() {
    return (
      <FieldWrapper label="Phone #">
        <Phone value={this.state.data.phone}
               onChange={this.onChangePhone} />
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
                  value={this.state.data.language_id}
                  onChange={this.onChangeLanguage} />
      </FieldWrapper>
    );
  }

  renderTimezoneField() {
    const { timezones } = this.props;

    return (
      <FieldWrapper label="Time Zone">
        <Timezone timezones={timezones}
                  value={this.state.data.timezone}
                  onChange={this.onChangeTimezone} />
      </FieldWrapper>
    );
  }

  renderPasswordField() {
    return (
      <FieldWrapper label="Password">
        <Password
          value={this.state.data.password}
          onChange={this.onChangePassword} />
      </FieldWrapper>
    );
  }

  static renderSubmitIcon() {
    return (
      <div>
        Saving...
      </div>
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
            {this.state.submit ? ProfileForm.renderSubmitIcon() : null}
            <input type="submit" onClick={this.submitForm} />
          </div>
        </div>
      </form>
    );
  }
}
