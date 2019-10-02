import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import moment from 'moment';
import BillingSummary from './BillingSummary';
import { allAccountsSelector, isAccountsLoadedSelector } from '../../Selectors/account';
import LoadingPage from '../../../Common/Components/LoadingPage';
import { loadAccounts } from '../../Actions/accountActions';
import { loadBillingSummary } from '../../Actions/usageActions';

@connect(state => ({
  accounts:       allAccountsSelector(state),
  accountsLoaded: isAccountsLoadedSelector(state),
}))
class BillingSummaryContainer extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func,
    accountsLoaded: PropTypes.bool,
    accounts:       PropTypes.object
  };

  constructor(props) {
    super(props);
    const now = moment();

    this.state = {
      account:       null,
      date:          `01-${now.format('MM')}-${now.format('YYYY')}`,
      records:       Immutable.fromJS([]),
      recordsLoaded: false
    };
  }

  componentDidMount() {
    const { dispatch, accounts } = this.props;
    dispatch(loadAccounts());

    this.preSelectAccount(accounts);
  }

  componentWillReceiveProps(newProps) {
    this.preSelectAccount(newProps.accounts);
  }

  preSelectAccount(accounts) {
    const { account } = this.state;
    if (accounts.size && !account) {
      setTimeout(() => this.changeAccount(accounts.first().get('id')), 1);
    }
  }

  changeAccount = (accountId) => {
    const { accounts } = this.props;
    this.setState({ account: accounts.get(accountId) }, this.loadStat);
  };

  changeDate = (date) => {
    this.setState({ date }, this.loadStat);
  };

  loadStat = () => {
    this.setState({
      recordsLoaded: false
    });

    const { account, date } = this.state;
    if (account) {
      const promise = this.props.dispatch(loadBillingSummary(account.get('id'), date));
      promise.then((records) => {
        this.setState({ records, recordsLoaded: true });
      });
    }
  };

  render() {
    const { accountsLoaded } = this.props;
    const { recordsLoaded } = this.state;

    if (!accountsLoaded || !recordsLoaded) {
      return <LoadingPage />;
    }

    return (
      <BillingSummary
        {...this.props}
        {...this.state}
        changeAccount={this.changeAccount}
        changeDate={this.changeDate}
      />
    );
  }
}

export default BillingSummaryContainer;
