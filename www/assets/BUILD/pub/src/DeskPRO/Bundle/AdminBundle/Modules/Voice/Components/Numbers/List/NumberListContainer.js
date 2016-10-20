import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import NumberList from './NumberList';
import { loadAccounts } from '../../../Actions/accountActions';
import { loadNumbers, collapseNumber } from '../../../Actions/numberActions';
import { loadQueues } from '../../../Actions/queueActions';
import { isAccountsLoadedSelector, allAccountsSelector } from '../../../Selectors/account';
import { allNumbersSelector, isNumbersLoadedSelector, expandedNumberSelector } from '../../../Selectors/numbers';
import { replaceRoute } from '../../../../../Services/history';
import { allQueuesSelector, isQueuesLoadedSelector } from '../../../Selectors/queue';

@connect(state => ({
  accounts:       allAccountsSelector(state),
  accountsLoaded: isAccountsLoadedSelector(state),
  numbers:        allNumbersSelector(state),
  numbersLoaded:  isNumbersLoadedSelector(state),
  queues:         allQueuesSelector(state),
  queuesLoaded:   isQueuesLoadedSelector(state),
  expandedNumber: expandedNumberSelector(state)
}))
class NumberListContainer extends React.Component {

  static propTypes = {
    dispatch:       PropTypes.func,
    accountsLoaded: PropTypes.bool,
    numbersLoaded:  PropTypes.bool,
    queuesLoaded:   PropTypes.bool,
    expandedNumber: PropTypes.number
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

  onCollapseNumber = (number) => {
    const { expandedNumber, dispatch } = this.props;

    if (number.get('id') === expandedNumber) {
      dispatch(collapseNumber());
    }
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
        onCollapseNumber={this.onCollapseNumber}
      />
    );
  }
}

export default NumberListContainer;
