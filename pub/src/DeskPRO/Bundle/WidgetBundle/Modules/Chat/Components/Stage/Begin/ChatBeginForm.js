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
      <div className="dpdesignportal-open-new-ticket">
        <form className="dpdesignportal-form" onSubmit={onSubmit}>
          <div>
            <label>Your Details</label>
            <input type="text" placeholder="First name, Last name" value={name} onChange={onChangeName} />
          </div>

          <div>
            <label>Your Email</label>
            <input type="text" placeholder="email@example.com" value={email} onChange={onChangeEmail} />
          </div>

          <div className="button-label">
            <input type="submit" value="Start a new chat" className="dpdesignportal-button dpdesignportal-button-wide" />
          </div>
        </form>

      </div>
    );
  }
}
