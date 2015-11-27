import React, { PropTypes } from 'react';

export class ChatBeginForm extends React.Component {

  static propTypes = {
    name: PropTypes.string,
    email: PropTypes.string,
    onChangeName: PropTypes.func,
    onChangeEmail: PropTypes.func,
    onSubmit: PropTypes.func
  };

  render() {
    const { name, email } = this.props;
    const { onChangeName, onChangeEmail, onSubmit } = this.props;

    return (
      <div>
        <form onSubmit={onSubmit}>
          <input type="text" name={name} onChange={onChangeName} />
          <input type="text" name={email} onChange={onChangeEmail} />

          <input type="submit" />
        </form>

        <div className="dpdesignportal-content dpdesignportal-open-new-ticket">
          <div className="header">
            <h1>Open a new ticket</h1>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut.</p>
          </div>

          <form className="dpdesignportal-form">
            <h2>Open new ticket</h2>

            <div>
              <label>Your Details</label>
              <input type="text" placeholder="First name, Last name" />
            </div>

            <div>
              <label>Pick the department you want to contact:</label>
              <span className="dpdesignportal-form-item-dropdown">Tech Support<i className="fa fa-caret-down"></i></span>
            </div>

            <div>
              <label>Software version:</label>
              <span className="dpdesignportal-form-item-dropdown">v2.1<i className="fa fa-caret-down"></i></span>
            </div>

            <div>
              <label>Message:</label>
              <textarea></textarea>
            </div>

            <div className="button-label">
              <input type="submit" value="Open a ticket" className="dpdesignportal-button dpdesignportal-button-wide" />
            </div>
          </form>

        </div>
      </div>
    );
  }
}
