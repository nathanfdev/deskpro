import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';

class AccountChoiceWrapper extends React.Component {

  static propTypes = {
    accounts: PropTypes.object,
    children: PropTypes.node
  };

  render() {
    const { children, accounts = Immutable.fromJS([]) } = this.props;
    const choices = accounts.toArray().map(account => ({
      value: account.get('id'),
      label: account.get('account_name')
    }));

    return React.cloneElement(children, { ...children.props, ...this.props, choices });
  }
}

export default AccountChoiceWrapper;
