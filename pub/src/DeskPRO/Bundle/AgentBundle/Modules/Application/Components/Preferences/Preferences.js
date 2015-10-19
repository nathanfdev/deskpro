import React, { PropTypes } from 'react';
import * as AppActions from '../../Actions/AppActions';

export class Preferences extends React.Component {

  static propTypes = {
    dpWindow: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  close = () => {
    this.props.dispatch(AppActions.closePreferences());
  };

  render() {
    return (
      <section className="popup no-footer" id="popup">
        <header>
          <h1>Account Preferences</h1>
          <div className="controls">
            <a href="#" onClick={this.close}><i className="fa fa-times"></i></a>
          </div>
        </header>

        <div className="popup-sidebar" id="popup-sidebar">
          <div className="popup-sidebar-content">
            <ul>
              <li className="active"><a href="popup-user-preferences.html">Profile</a></li>
              <li><a href="popup-signature.html">Signature</a></li>
              <li><a href="popup-general-settings.html">Settings</a></li>
              <li>
                <a href="popup-notification-settings.html">Notifications</a>
                <ul className="stat-types-list">
                  <li><a href="#">Inbox</a></li>
                  <li><a href="#">Everything Else</a></li>
                </ul>
              </li>
              <li><a href="popup-devices.html">Devices</a></li>
            </ul>
          </div>
        </div>

        <div className="popup-content">
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
      </section>
    );
  }
}
