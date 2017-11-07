import PropTypes from 'prop-types';
import React from 'react';
import Loader from 'react-loader';
import { updateMyProfile } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/profile';
import { Field } from './Fields/Field';
import { Avatar } from './Fields/Avatar';
import { Name } from './Fields/Name';
import { DisplayName } from './Fields/DisplayName';
import { Email } from './Fields/Email';
import { PrimaryEmail } from './Fields/PrimaryEmail';
import { Phone } from './Fields/Phone';
import { Language } from './Fields/Language';
import { Timezone } from './Fields/Timezone';
import { Password } from './Fields/Password';

export class ProfileForm extends React.Component {
  static propTypes = {
    dispatch:  PropTypes.func.isRequired,
    languages: PropTypes.object.isRequired,
    timezones: PropTypes.object.isRequired,
    profile:   PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    const profile = props.profile;
    const avatar  = profile.get('avatar');
    const phone   = profile.get('phone');

    this.state = {
      data: {
        name:          profile.get('name'),
        display_name:  profile.get('display_name'),
        emails:        profile.get('emails').toArray() || [],
        primary_email: profile.get('primary_email'),
        language_id:   profile.get('language_id'),
        timezone:      profile.get('timezone') || 'UTC',

        avatar: {
          blob_auth_id: avatar && avatar.get('blob_auth_id'),
          url:          avatar && avatar.get('url')
        },

        phone: {
          number:    phone && phone.get('number'),
          extension: phone && phone.get('extension')
        },

        password: {
          first:  null,
          second: null
        }
      },

      errors: {},
      submit: false
    };
  }

  onChangeAvatar = (value) => {
    this.updateData(
      {
        avatar: {
          url:          value ? this.state.data.avatar.url : null,
          blob_auth_id: value
        }
      });
  };

  onChangeName = (value) => {
    this.updateData({ name: value });
  };

  onChangeDisplayName = (value) => {
    this.updateData({ display_name: value });
  };

  onChangeEmails = (emails) => {
    const diff = { emails };
    if (emails.length) {
      diff.primary_email = emails[0];
    }

    this.updateData(diff);
  };

  onChangePrimaryEmail = (value) => {
    this.updateData({ primary_email: value });
  };

  onChangePhone = (number, extension) => {
    this.updateData({ phone: { number, extension } });
  };

  onChangeLanguage = (value) => {
    this.updateData({ language_id: value });
  };

  onChangeTimezone = (value) => {
    this.updateData({ timezone: value });
  };

  onChangePassword = (first, second) => {
    this.updateData({ password: { first, second } });
  };

  updateData(diff) {
    const oldData = this.state.data;
    this.setState({ data: { ...oldData, ...diff } });
  }

  submitForm = (event) => {
    event.preventDefault();
    this.setState({ submit: true, errors: {} });

    const { dispatch } = this.props;
    const stateData  = this.state.data;
    const submitData = { ...stateData };

    if (submitData.avatar.blob_auth_id) {
      submitData.avatar_blob_auth_id = submitData.avatar.blob_auth_id;
    }
    if (!submitData.phone.number) {
      delete submitData.phone;
    }

    delete submitData.avatar;

    const promise = dispatch(updateMyProfile(submitData));
    promise
      .success(() => this.setState({ submit: false }))
      .catch(result => this.setState({ submit: false, errors: result.getData().errors }))
    ;
  };

  renderNameField() {
    return (
      <Field label="Your name" name="name" errors={this.state.errors}>
        <Avatar
          personName={this.state.data.name}
          value={this.state.data.avatar.url}
          onChange={this.onChangeAvatar}
        />
        <Name value={this.state.data.name} onChange={this.onChangeName} />
      </Field>
    );
  }

  renderDisplayNameField() {
    return (
      <Field name="display_name" errors={this.state.errors}>
        <DisplayName value={this.state.data.display_name} onChange={this.onChangeDisplayName} />
      </Field>
    );
  }

  renderEmailField() {
    return (
      <Field label="Your email" name="emails" errors={this.state.errors}>
        <Email emails={this.state.data.emails} onChange={this.onChangeEmails} /> </Field>
    );
  }

  renderPrimaryEmailField() {
    const emails = this.state.data.emails;
    if (!emails || !emails.length || !emails[1]) {
      return null;
    }

    return (
      <Field label="Primary email" name="primary_email" errors={this.state.errors}>
        <PrimaryEmail
          emails={emails}
          value={this.state.data.primary_email}
          onChange={this.onChangePrimaryEmail}
        />
      </Field>
    );
  }

  renderPhoneField() {
    return (
      <Field label="Phone #" name="phone" errors={this.state.errors}>
        <Phone value={this.state.data.phone} onChange={this.onChangePhone} />
      </Field>
    );
  }

  renderLanguageField() {
    const { languages } = this.props;
    if (languages.size < 2) {
      return null;
    }

    return (
      <Field label="Language" name="language_id" errors={this.state.errors}>
        <Language
          languages={languages}
          value={this.state.data.language_id}
          onChange={this.onChangeLanguage}
        />
      </Field>
    );
  }

  renderTimezoneField() {
    const { timezones } = this.props;

    return (
      <Field label="Time Zone" name="timezone" errors={this.state.errors}>
        <Timezone
          timezones={timezones}
          value={this.state.data.timezone}
          onChange={this.onChangeTimezone}
        />
      </Field>
    );
  }

  renderPasswordField() {
    return (
      <Field label="Password" name="password" errors={this.state.errors}>
        <Password value={this.state.data.password} onChange={this.onChangePassword} />
      </Field>
    );
  }

  renderSubmitButton() {
    return (
      <div className="bucket">
        <div className="bucket-column submit">
          <Loader
            left="45%"
            opacity={0}
            width={3}
            loaded={!this.state.submit}
          >
            <input type="submit" value="Save" onClick={this.submitForm} />
          </Loader>
        </div>
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
        {this.renderSubmitButton()}
      </form>
    );
  }
}
