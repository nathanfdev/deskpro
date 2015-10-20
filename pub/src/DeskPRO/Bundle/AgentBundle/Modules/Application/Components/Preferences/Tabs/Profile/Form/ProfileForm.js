import React, { PropTypes } from 'react';
import { FieldWrapper } from './Fields/FieldWrapper';
import { Name } from './Fields/Name';
import { Avatar } from './Fields/Avatar';
import { Email } from './Fields/Email';
import { Phone } from './Fields/Phone';
import { Language } from './Fields/Language';
import { Password } from './Fields/Password';

export class ProfileForm extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    languages: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      name: null,
      overrideDefaultName: false,
      emails: [],
      primaryEmail: null,
      phone: null,
      language: null,
      timezone: null,
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

    if (emails.length === 1) {
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
  };

  renderNameAvatarField() {
    return (
      <FieldWrapper label="Your name">
        <Avatar />
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
               primary={this.state.primaryEmail}
               onChangeEmails={this.onChangeEmails}
               onChangePrimary={this.onChangePrimaryEmail} />
      </FieldWrapper>
    );
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
        <Language languages={languages} />
      </FieldWrapper>
    );
  }

  renderTimezoneField() {
    return (
      <FieldWrapper label="Time Zone">
        <div className="bucket-column">
          <a href="#" className="select">Europe/London (0 GMT) <i className="fa fa-caret-down"></i></a>
        </div>
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
        {this.renderNameAvatarField()}
        {this.renderOverrideDefaultNameField()}
        {this.renderEmailField()}
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
