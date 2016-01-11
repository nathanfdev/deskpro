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
      <div>
        <p>We require you to validate your email address.</p>
        <p>We sent you an email with a validation code. Check your email then enter the code below</p>

        <form>
          <input onChange={this.onChangeCode} value={this.state.code} />
          <a href="#" onClick={this.onSubmit}>
            Start Chat <i className="fa fa-chevron-right"></i>
          </a>
        </form>
      </div>
    );
  }
}
