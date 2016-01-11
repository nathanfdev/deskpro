import React from 'react';

export class ChatEmailValidation extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      code: ''
    };
  }

  onChangeCode = event => {
    this.setState({
      code: event.target.value
    });
  };

  onSubmit = event => {
    event.preventDefault();
    console.log(this.state.code);
  };

  render() {
    return (
      <div className="dpdesignportal-chat-email-validation">
        <span className="description">
          <p>We require you to validate your email address.</p>
          <br/>
          <p>We sent you an email with a validation code.</p>
          <p>Check your email then enter the code below</p>
        </span>

        <form>
          <input type="text" onChange={this.onChangeCode} value={this.state.code} />
          <a href="#" className="email-code-submit" onClick={this.onSubmit}>
            Start Chat <i className="fa fa-chevron-right"></i>
          </a>
        </form>
      </div>
    );
  }
}
