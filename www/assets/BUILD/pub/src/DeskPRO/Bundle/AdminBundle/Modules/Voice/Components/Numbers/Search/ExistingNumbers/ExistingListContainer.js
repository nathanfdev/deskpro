import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import { connect } from 'react-redux';
import ExistingList from './ExistingList';
import { loadExistingNumbers } from '../../../../Actions/numberActions';
import { isAccountsLoadedSelector, allAccountsSelector } from '../../../../Selectors/account';
import { isNumbersLoadedSelector } from '../../../../Selectors/numbers';
import BaseSearchContainer from '../BaseSearchContainer';
import { replaceRoute } from '../../../../../../Services/history';

@connect(state => ({
  accountsLoaded: isAccountsLoadedSelector(state),
  accounts:       allAccountsSelector(state),
  numbersLoaded:  isNumbersLoadedSelector(state)
}))
class ExistingNumbersContainer extends BaseSearchContainer {

  static propTypes = {
    accountsLoaded: PropTypes.bool,
    numbersLoaded:  PropTypes.bool,
    dispatch:       PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      loading: false,
      numbers: [],
      pageNum: 1,
      hasNext: false,
      filter:  null
    };
  }

  onAddNumber = (number) => {
    const params = {
      sid:     number.get('sid'),
      account: number.get('account'),
      number:  number.get('number')
    };

    replaceRoute(`/voice_channel/numbers/new?${compileParams(params)}`);
  };

  onChangeFilter = (filter) => {
    this.setState({
      loading: true,
      numbers: [],
      pageNum: 1,
      filter
    });

    if (filter.account) {
      const { accounts, dispatch } = this.props;
      const account = accounts.get(filter.account);
      if (account) {
        dispatch(loadExistingNumbers(account, 1)).success(this.onLoadNumbers);
      }
    }
  };

  onChangePage = (pageNum) => {
    const { accounts, dispatch } = this.props;
    this.setState({
      loading: true,
      numbers: [],
      pageNum
    });

    const account = accounts.get(this.state.filter.account);
    if (account) {
      dispatch(loadExistingNumbers(account, pageNum)).success(this.onLoadNumbers);
    }
  };

  onLoadNumbers = (result) => {
    this.setState({
      loading: false,
      numbers: Immutable.fromJS(result.data),
      pageNum: result.meta.page_num,
      hasNext: result.meta.has_next
    });
  };

  render() {
    const { numbersLoaded, accountsLoaded, accounts } = this.props;
    if (!numbersLoaded || !accountsLoaded) {
      return <LoadingPage />;
    }

    return (
      <ExistingList
        {...this.state}
        accounts={accounts}
        onClickBack={this.onClickBack}
        onChangeFilter={this.onChangeFilter}
        onAddNumber={this.onAddNumber}
        onChangePage={this.onChangePage}
      />
    );
  }
}

export default ExistingNumbersContainer;
