import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { LoginForm } from './Form/LoginForm';
import { hasAuthSelector } from '../Selectors/login';

@connect(state => ({
  hasAuth: hasAuthSelector(state)
}))
export class LoginApp extends React.Component {

  static propTypes = {
    history: PropTypes.object.isRequired,
    hasAuth: PropTypes.bool.isRequired
  };

  componentDidMount() {
    const { hasAuth, history } = this.props;

    if (hasAuth) {
      history.replace(`${DP_BASE_URL_RELATIVE}/agent/`);
    }
  }

  render() {
    return <LoginForm />;
  }
}
