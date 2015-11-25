import React from 'react';
import { Header } from './Header';
import { UserInfoForm } from './UserInfoForm';
import { Checkbox } from './Checkbox';

export class ChatBeginConversation extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      name: '',
      email: '',
      hiddenEmail: false,
      step: 'name'
    };
  }

  onChangeStep = value => {
    this.setState({
      step: value
    });
  };

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

  onToggleHiddenEmail = () => {
    this.setState({
      hiddenEmail: !this.state.hiddenEmail
    });
  };

  onSubmit = () => {
    console.log('submit form');
  };

  renderNameForm() {
    return (
      <UserInfoForm title="Just so we know lorel ipsum, what's your name?" onSubmit={() => this.onChangeStep('email')}>
        <input type="text"
               placeholder="First & last name"
               value={this.state.name}
               onChange={this.onChangeName} />

        <input type="submit" value="Go" />
      </UserInfoForm>
    );
  }

  renderEmailForm() {
    return (
      <UserInfoForm title="What's your email address so we can lorel ipsum?" onSubmit={this.onSubmit}>
        <input type="text"
               placeholder="email@example.com"
               value={this.state.email}
               onChange={this.onChangeEmail} />

        <input type="submit" value="Go" />
        <Checkbox value={this.state.hiddenEmail} onToggle={this.onToggleHiddenEmail} />
      </UserInfoForm>
    );
  }

  render() {
    const step = this.state.step;

    return (
      <div>
        <Header />

        {step === 'name' && this.renderNameForm()}
        {step === 'email' && this.renderEmailForm()}
      </div>
    );
  }
}
