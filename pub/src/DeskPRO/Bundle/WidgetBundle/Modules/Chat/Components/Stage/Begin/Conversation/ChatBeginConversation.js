import React, { PropTypes } from 'react';
import { UserInfoForm } from './UserInfoForm';
import { Checkbox } from './Checkbox';

export class ChatBeginConversation extends React.Component {

  static propTypes = {
    name: PropTypes.string,
    email: PropTypes.string,
    hiddenEmail: PropTypes.bool,
    onChangeName: PropTypes.func,
    onChangeEmail: PropTypes.func,
    onToggleHiddenEmail: PropTypes.func,
    onSubmit: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      step: 'name'
    };
  }

  onChangeStep = value => {
    this.setState({
      step: value
    });
  };

  renderNameForm() {
    const { name, onChangeName } = this.props;

    return (
      <UserInfoForm title="Just so we know lorel ipsum, what's your name?"
                    onSubmit={() => this.onChangeStep('email')}>

        <input type="text"
               placeholder="First & last name"
               value={name}
               onChange={onChangeName} />

        <input type="submit" value="Go" />
      </UserInfoForm>
    );
  }

  renderEmailForm() {
    const { email, hiddenEmail } = this.props;
    const { onChangeEmail, onToggleHiddenEmail, onSubmit } = this.props;

    return (
      <UserInfoForm title="What's your email address so we can lorel ipsum?"
                    onSubmit={onSubmit}>

        <input type="text"
               placeholder="email@example.com"
               value={email}
               onChange={onChangeEmail} />

        <input type="submit" value="Go" />
        <Checkbox value={hiddenEmail} onToggle={onToggleHiddenEmail} />
      </UserInfoForm>
    );
  }

  render() {
    const step = this.state.step;

    return (
      <div>
        {step === 'email' ? this.renderEmailForm() : this.renderNameForm()}
      </div>
    );
  }
}
