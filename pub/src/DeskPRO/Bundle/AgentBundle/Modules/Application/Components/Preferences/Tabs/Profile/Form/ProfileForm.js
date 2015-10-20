import React from 'react';

export class ProfileForm extends React.Component {
  render() {
    return (
      <div>
        <form className="popup-form-default">
          <div className="bucket">
            <label className="label">Your name:</label>
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
          </div>

          <div className="bucket short">
            <label className="simple-label"><input type="checkbox" /> Override default name?</label>
            <span className="small">(will be displayed to users instead of your real name.)</span>
          </div>

          <div className="bucket">
            <label className="label">Your email:</label>
            <div className="bucket-column-last">
              <span className="meta"><a href="#">Add more emails</a></span>
            </div>
            <div className="bucket-column">
              <input type="text" placeholder="Your email" value="dennis.schipper@deskpro.com" />
            </div>
          </div>

          <div className="bucket">
            <label className="label">Phone #:</label>
            <div className="bucket-column">
              <input type="text" placeholder="Your phone number" />
            </div>
          </div>

          <hr />

          <div className="bucket">
            <label className="label">Language:</label>
            <div className="bucket-column">
              <a href="#" className="select">English <i className="fa fa-caret-down"></i></a>
            </div>
          </div>

          <div className="bucket">
            <label className="label">Time Zone:</label>
            <div className="bucket-column">
              <a href="#" className="select">Europe/London (0 GMT) <i className="fa fa-caret-down"></i></a>
            </div>
          </div>

          <hr />

          <div className="bucket bucket-short">
            <label className="label">Password:</label>
            <div className="bucket-column">
              <a href="#" className="button button-secondary button-short">Change Password</a>
            </div>
          </div>
        </form>
      </div>
    );
  }
}
