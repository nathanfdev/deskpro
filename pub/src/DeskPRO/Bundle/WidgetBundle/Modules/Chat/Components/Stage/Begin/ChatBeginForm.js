import React from 'react';

export class ChatBeginForm extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      name: '',
      email: ''
    };
  }

  onChangeName = event => {
    this.setState({
      name: event.target.value
    });
  };

  onChangeEmail = event => {
    this.setState({
      email: event.target.value
    });
  };

  onSubmit = event => {
    event.preventDefault();
    console.log('submit form');
  };

  render() {
    return (
      <div>
        Begin form mode

        <form onSubmit={this.onSubmit}>
          <input type="text" name={this.state.name} onChange={this.onChangeName} />
          <input type="text" name={this.state.email} onChange={this.onChangeEmail} />

          <input type="submit" />
        </form>
      </div>
    );
  }
}
