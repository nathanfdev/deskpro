import React from 'react';
import { FieldWrapper } from './FieldWrapper';

export class ProfileForm extends React.Component {
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

          <div className="bucket-column">
            <input type="text" placeholder="Your name" value="Dennis Schipper" />
          </div>
        </FieldWrapper>

        <div className="bucket short">
          <label className="simple-label"><input type="checkbox" /> Override default name?</label>
          <span className="small">(will be displayed to users instead of your real name.)</span>
        </div>

        <FieldWrapper label="Your email">
          <div className="bucket-column-last">
            <span className="meta"><a href="#">Add more emails</a></span>
          </div>
          <div className="bucket-column">
            <input type="text" placeholder="Your email" value="dennis.schipper@deskpro.com" />
          </div>
        </FieldWrapper>

        <FieldWrapper label="Phone #">
          <div className="bucket-column">
            <input type="text" placeholder="Your phone number" />
          </div>
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

        <div className="bucket bucket-short">
          <label className="label">Password:</label>
          <div className="bucket-column">
            <a href="#" className="button button-secondary button-short">Change Password</a>
          </div>
        </div>
      </form>
    );
  }
}
