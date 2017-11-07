import PropTypes from 'prop-types';
import React from 'react';
import { replaceRoute } from 'DeskPRO/Bundle/AdminBundle/Services/history';
import { loadAccounts } from '../../../Actions/accountActions';
import { loadNumbers } from '../../../Actions/numberActions';

class BaseSearchContainer extends React.Component {

  static propTypes = {
    filter:   PropTypes.object,
    accounts: PropTypes.object,
    dispatch: PropTypes.func
  };

  componentDidMount() {
    const { filter, accounts, dispatch } = this.props;

    dispatch(loadNumbers());
    dispatch(loadAccounts());

    this.preSelectAccount(filter, accounts);

    if (filter.account) {
      this.onChangeFilter(filter);
    }
  }

  componentWillReceiveProps(newProps) {
    this.preSelectAccount(newProps.filter, newProps.accounts);
  }

  onClickBack = () => {
    replaceRoute('/voice_channel/numbers');
  };

  preSelectAccount(filter, accounts) {
    if (accounts.size && !filter.account) {
      this.onChangeFilter({ ...filter, account: accounts.first().get('id') });
    }
  }
}

export default BaseSearchContainer;
