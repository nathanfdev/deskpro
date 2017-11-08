import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import NumberList from './NumberList';
import { loadAccounts } from '../../../Actions/accountActions';
import { loadNumbers } from '../../../Actions/numberActions';
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
    queuesLoaded:   PropTypes.bool
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAccounts());
    dispatch(loadNumbers());
    dispatch(loadQueues());
  }

  onGoToAccounts = () => {
    replaceRoute('/voice_channel/accounts');
  };

  onAddNumber = () => {
    replaceRoute('/voice_channel/numbers/available');
  };

  onAddExistingNumber = () => {
    replaceRoute('/voice_channel/numbers/existing');
  };

  onEditNumber = (number) => {
    replaceRoute(`/voice_channel/numbers/${number.get('id')}`);
  };

  render() {
    const { accountsLoaded, numbersLoaded, queuesLoaded } = this.props;

    if (!accountsLoaded || !numbersLoaded || !queuesLoaded) {
      return <LoadingPage />;
    }

    return (
      <NumberList
        {...this.props}
        onGoToAccounts={this.onGoToAccounts}
        onAddNumber={this.onAddNumber}
        onAddExistingNumber={this.onAddExistingNumber}
        onEditNumber={this.onEditNumber}
      />
    );
  }
}

export default NumberListContainer;
