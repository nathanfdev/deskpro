import React, { PropTypes } from 'react';
import { FieldWrapper } from './Fields/FieldWrapper';
import { Name } from './Fields/Name';
import { Email } from './Fields/Email';
import { Phone } from './Fields/Phone';
import { Password } from './Fields/Password';

export class ProfileForm extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      name: null,
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

  submitForm = (event) => {
    event.preventDefault();
  };

  render() {
    return (
      <form className="popup-form-default">
        <FieldWrapper label="Your name">
          <div className="bucket-column-last">
            <a href="#" className="button button-secondary user-avatar"><span style={{backgroundImage: 'url(./img/avatar2.png)'}}></span>Manage Avatar</a>

            <div className="avatar-crop" id="avatar-crop" style={{display: 'none'}}>
              <p>Click &amp; drag to crop your avatar</p>
              <div className="cropper-bucket">
                <img src="./img/avatar-sample.png" alt="Avatar Sample" />
              </div>
              <a href="#" className="crop">Crop &amp; Save Avatar</a>
              <a href="#" className="cancel">Or cancel &amp; discard your changes</a>
            </div>
          </div>

          <Name value={this.state.name} onChange={this.onChangeName} />
        </FieldWrapper>

        <div className="bucket short">
          <label className="simple-label"><input type="checkbox" /> Override default name?</label>
          <span className="small">(will be displayed to users instead of your real name.)</span>
        </div>

        <FieldWrapper label="Your email">
          <Email emails={this.state.emails}
                 primary={this.state.primaryEmail}
                 onChangeEmails={this.onChangeEmails}
                 onChangePrimary={this.onChangePrimaryEmail} />
        </FieldWrapper>

        <FieldWrapper label="Phone #">
          <Phone value={this.state.phone} onChange={this.onChangePhone} />
        </FieldWrapper>

        <hr />

        <FieldWrapper label="Language">
          <div className="bucket-column">
            <a href="#" className="select">English <i className="fa fa-caret-down"></i></a>
          </div>
        </FieldWrapper>

        <FieldWrapper label="Time Zone">
          <div className="bucket-column">
            <a href="#" className="select">Europe/London (0 GMT) <i className="fa fa-caret-down"></i></a>
          </div>
        </FieldWrapper>

        <hr />

        <FieldWrapper label="Password">
          <Password value={this.state.password} confirmValue={this.state.confirmPassword} />
        </FieldWrapper>

        <input type="submit" onClick={this.submitForm} />
      </form>
    );
  }
}
