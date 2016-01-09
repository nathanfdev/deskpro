import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { login } from '../../Actions/loginActions';
import { LoginForm } from './LoginForm';

@connect()
export class LoginFormContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      submit: false,
      errors: null
    };
  }

  onSubmitForm = data => {
    event.preventDefault();
    if (this.state.submit) {
      return;
    }

    const { dispatch } = this.props;
    const promise = dispatch(login(data));

    this.setState({
      submit: true
    });

    promise.then(
      () => {
        this.setState({
          submit: false
        });
      },
      response => {
        this.setState({
          submit: false,
          errors: response.getData().errors
        });
      }
    );
  };

  render() {
    return (
      <LoginForm submit={this.state.submit}
                 errors={this.state.errors}
                 onSubmitForm={this.onSubmitForm} />
    );
  }
}
