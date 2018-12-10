import PropTypes from 'prop-types';
import React from 'react';
import { replaceRoute } from 'DeskPRO/Bundle/AdminBundle/Services/history';
import { loadAccounts } from '../../../Actions/accountActions';
import { loadNumbers } from '../../../Actions/numberActions';

class BaseSearchContainer extends React.Component {

  static propTypes = {
    accounts: PropTypes.object,
    dispatch: PropTypes.func
  };

  componentDidMount() {
    const { accounts, dispatch } = this.props;
    const { filter } = this.state;

    dispatch(loadNumbers());
    dispatch(loadAccounts());

    this.preSelectAccount(filter, accounts);

    if (filter && filter.account) {
      this.onChangeFilter(filter);
    }
  }

  componentWillReceiveProps(newProps) {
    this.preSelectAccount(this.state.filter, newProps.accounts);
  }

  onClickBack = () => {
    replaceRoute('/voice_channel/numbers');
  };

  preSelectAccount(filter, accounts) {
    if (accounts.size && (!filter || !filter.account)) {
      setTimeout(() => this.onChangeFilter({ ...filter, account: accounts.first().get('id') }), 1);
    }
  }
}

export default BaseSearchContainer;
