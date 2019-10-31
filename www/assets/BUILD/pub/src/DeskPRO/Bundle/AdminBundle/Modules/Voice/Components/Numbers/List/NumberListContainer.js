import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import NumberList from './NumberList';
import { loadAccounts } from '../../../Actions/accountActions';
import { loadNumbers, loadExistingNumbers, releaseNumber } from '../../../Actions/numberActions';
import { loadQueues } from '../../../Actions/queueActions';
import { isAccountsLoadedSelector, allAccountsSelector } from '../../../Selectors/account';
import { allNumbersSelector, isNumbersLoadedSelector } from '../../../Selectors/numbers';
import { replaceRoute } from '../../../../../Services/history';
import { allQueuesSelector, isQueuesLoadedSelector } from '../../../Selectors/queue';

@connect(state => ({
  accounts:       allAccountsSelector(state),
  accountsLoaded: isAccountsLoadedSelector(state),
  numbers:        allNumbersSelector(state),
  numbersLoaded:  isNumbersLoadedSelector(state),
  queues:         allQueuesSelector(state),
  queuesLoaded:   isQueuesLoadedSelector(state)
}))
class NumberListContainer extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func,
    accountsLoaded: PropTypes.bool,
    numbersLoaded:  PropTypes.bool,
    queuesLoaded:   PropTypes.bool,
    accounts:       PropTypes.bool,
  };

  constructor(props) {
    super(props);
    this.state = {
      disabledLoading: false,
      disabledNumbers: [],
      disabledFilter:  null
    };
  }

  componentDidMount() {
    const { dispatch, accounts } = this.props;
    const { disabledFilter } = this.state;

    dispatch(loadAccounts());
    dispatch(loadNumbers());
    dispatch(loadQueues());

    this.preSelectAccount(disabledFilter, accounts);

    if (disabledFilter && disabledFilter.account) {
      this.changeDisabledFilter(disabledFilter);
    }
  }

  componentWillReceiveProps(newProps) {
    this.preSelectAccount(this.state.disabledFilter, newProps.accounts);
  }

  preSelectAccount(disabledFilter, accounts) {
    if (accounts.size && (!disabledFilter || !disabledFilter.account)) {
      setTimeout(() => this.changeDisabledFilter({ ...disabledFilter, account: accounts.first().get('id') }), 1);
    }
  }

  goToAccounts = () => {
    replaceRoute('/voice_channel/accounts');
  };

  goToAvailableNumbers = () => {
    replaceRoute('/voice_channel/numbers/available');
  };

  editNumber = (number) => {
    replaceRoute(`/voice_channel/numbers/${number.get('id')}`);
  };

  enableNumber = (number) => {
    const params = {
      sid:     number.get('sid'),
      account: number.get('account'),
      number:  number.get('number')
    };

    replaceRoute(`/voice_channel/numbers/new?${compileParams(params)}`);
  };

  releaseNumber = (number) => {
    const { accounts, dispatch } = this.props;
    const { disabledNumbers, disabledFilter } = this.state;
    if (disabledFilter && disabledFilter.account) {
      const account = accounts.get(disabledFilter.account);
      if (account) {
        const promise = dispatch(releaseNumber(account, number));
        promise.success(() => {
          const index = disabledNumbers.indexOf(number);
          if (index !== -1) {
            this.setState({
              disabledNumbers: disabledNumbers.delete(index)
            });
          }
        });
      }
    }
  };

  changeDisabledFilter = (disabledFilter) => {
    this.setState({
      disabledLoading: true,
      disabledNumbers: [],
      disabledFilter
    });

    if (disabledFilter.account) {
      const { accounts, dispatch } = this.props;
      const account = accounts.get(disabledFilter.account);
      if (account) {
        dispatch(loadExistingNumbers(account)).success(this.loadDisabledNumbers);
      }
    }
  };

  loadDisabledNumbers = (result) => {
    this.setState({
      disabledLoading: false,
      disabledNumbers: Immutable.fromJS(result.data)
    });
  };

  render() {
    const { accountsLoaded, numbersLoaded, queuesLoaded } = this.props;

    if (!accountsLoaded || !numbersLoaded || !queuesLoaded) {
      return <LoadingPage />;
    }

    return (
      <NumberList
        {...this.props}
        {...this.state}
        goToAccounts={this.goToAccounts}
        goToAvailableNumbers={this.goToAvailableNumbers}
        enableNumber={this.enableNumber}
        releaseNumber={this.releaseNumber}
        editNumber={this.editNumber}
        changeDisabledFilter={this.changeDisabledFilter}
      />
    );
  }
}

export default NumberListContainer;
