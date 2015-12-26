import React, { PropTypes } from 'react';
import { UserInfoForm } from './UserInfoForm';
import { Checkbox } from './Checkbox';
import { hasErrors, FieldErrors } from 'DeskPRO/Component/Form/FormErrors';

export class ChatBeginConversation extends React.Component {

  static propTypes = {
    name: PropTypes.string,
    email: PropTypes.string,
    hiddenEmail: PropTypes.bool,
    submit: PropTypes.bool,
    errors: PropTypes.object,
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
    const { name, onChangeName, errors } = this.props;

    return (
      <UserInfoForm title="Just so we know lorel ipsum, what's your name?"
                    onSubmit={() => this.onChangeStep('email')}
                    error={hasErrors(errors, 'name')}>

        <input type="text"
               placeholder="First & last name"
               value={name}
               onChange={onChangeName} />

        <input type="submit" value="Go" />
      </UserInfoForm>
    );
  }

  renderEmailForm() {
    const { email, hiddenEmail, submit, errors } = this.props;
    const { onChangeEmail, onToggleHiddenEmail, onSubmit } = this.props;

    return (
      <UserInfoForm title="What's your email address so we can lorel ipsum?"
                    onSubmit={onSubmit}
                    error={hasErrors(errors, 'email')}>

        <input type="text"
               placeholder="email@example.com"
               value={email}
               onChange={onChangeEmail} />

        <FieldErrors errors={errors} name="email" />

        {submit
          ? <div className="spinner"><i/></div>
          : <input type="submit" value="Go" />
        }

        {false && /* disabled for now */ <Checkbox value={hiddenEmail} onToggle={onToggleHiddenEmail} />}
      </UserInfoForm>
    );
  }

  render() {
    return (
      <div>
        {this.state.step === 'email' ? this.renderEmailForm() : this.renderNameForm()}
      </div>
    );
  }
}
