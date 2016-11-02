import React from 'react';
import { EmailsAndBlockMenuContainer } from './Menus/EmailsAndBlockMenu';
import DropDownMenu from './Menus/DropDownMenu';

class EmailTemplatesEditorContainer extends React.Component {
  render() {
    return (
      <div className="dp-email-templates">
        <div className="editor">
          <div className="header">
            <div>
              <button className="ui button basic">
                <i className="icon users" />
                User email templates
                <i className="fa fa-caret-down" />
              </button>
              <button className="ui button basic">
                <i className="flag gb" />
                English
                <i className="fa fa-caret-down" />
              </button>
              <button className="ui right floated button">
                + New Template
              </button>
            </div>
            <div>
              <div className="menu">
                <DropDownMenu
                  icon="mail"
                  label="Register Welcome Email"
                >
                  <EmailsAndBlockMenuContainer />
                </DropDownMenu>
              </div>
              <div className="menu right floated">
                <div>
                  <i className="icon dollar" />
                  Variables
                  <i className="fa fa-caret-down" />
                </div>
              </div>
              <div className="menu right floated">
                <div>
                  <i className="icon globe" />
                  Phrases
                  <i className="fa fa-caret-down" />
                </div>
              </div>
              <div className="menu right floated">
                <div>
                  <i className="icon image" />
                  Media
                  <i className="fa fa-caret-down" />
                </div>
              </div>
            </div>
          </div>
          <div className="dp-code-editor">
            Email subject:
            <div className="email-subject with-ace-editor" />
            Email:
            <div className="email-body with-ace-editor" />
          </div>
          <div className="footer">
            <button className="ui primary small button">Save changes</button>
            <button className="ui basic small button">Undo changes</button>
            <button className="ui right floated basic small button">Reset template</button>
          </div>
        </div>
        <div className="preview">
          <div className="header">
            <p>
              Enter details below to send a test email to yourself or a colleague.
            </p>
            <form className="ui form">
              <div className="fields">
                <div className="six wide field">
                  <label htmlFor="test_email_from">From</label>
                  <div className="ui selection dropdown" id="test_email_from">
                    <input type="hidden" />
                    <i className="dropdown icon" />
                    <span className="item">support@deskpro.com</span>
                  </div>
                </div>
                <div className="six wide field">
                  <label htmlFor="test_email_to">To</label>
                  <input type="text" name="to" id="test_email_to" />
                </div>
                <div className="four wide field">
                  <label htmlFor="test_email_submit">&nbsp;</label>
                  <input type="submit" className="ui basic button" value="Send" id="test_email_submit" />
                </div>
              </div>
            </form>

          </div>
          <div className="email-preview">
            <div className="email">
              Test preview email
            </div>
          </div>
        </div>
      </div>
    );
  }
}
export default EmailTemplatesEditorContainer;
