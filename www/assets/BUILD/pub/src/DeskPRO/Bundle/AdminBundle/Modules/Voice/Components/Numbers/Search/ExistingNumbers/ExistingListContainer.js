import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import { connect } from 'react-redux';
import ExistingList from './ExistingList';
import { loadExistingNumbers, addExistingNumber, changeExistingNumbersFilter } from '../../../../Actions/numberActions';
import { isAccountsLoadedSelector, allAccountsSelector } from '../../../../Selectors/account';
import { isNumbersLoadedSelector, existingNumbersFilterSelector } from '../../../../Selectors/numbers';
import BaseSearchContainer from '../BaseSearchContainer';
import { replaceRoute } from '../../../../../../Services/history';

@connect(state => ({
  accountsLoaded: isAccountsLoadedSelector(state),
  accounts:       allAccountsSelector(state),
  numbersLoaded:  isNumbersLoadedSelector(state),
  filter:         existingNumbersFilterSelector(state)
}))
class ExistingNumbersContainer extends BaseSearchContainer {

  static propTypes = {
    accountsLoaded: PropTypes.bool,
    numbersLoaded:  PropTypes.bool,
    filter:         PropTypes.object,
    dispatch:       PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      loading: false,
      numbers: [],
      pageNum: 1,
      hasNext: false
    };
  }

  onAddNumber = (number) => {
    this.setState({
      loading: true
    });

    const promise = this.props.dispatch(addExistingNumber(number));
    promise.success((response) => {
      this.setState({
        loading: false
      }, () => replaceRoute(`/voice_channel/numbers/${response.data.id}`));
    });
    promise.error(() => {
      this.setState({
        loading: false
      });
    });
  };

  onChangeFilter = (filter) => {
    this.setState({
      loading: true,
      numbers: [],
      pageNum: 1
    });

    const { dispatch } = this.props;

    dispatch(changeExistingNumbersFilter(filter));
    dispatch(loadExistingNumbers(filter.account, 1)).success(this.onLoadNumbers);
  };

  onChangePage = (pageNum) => {
    this.setState({
      loading: true,
      numbers: [],
      pageNum
    });

    const { filter, dispatch } = this.props;
    dispatch(loadExistingNumbers(filter.account, pageNum)).success(this.onLoadNumbers);
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
    const { numbersLoaded, accountsLoaded, filter, accounts } = this.props;
    if (!numbersLoaded || !accountsLoaded) {
      return <LoadingPage />;
    }

    return (
      <ExistingList
        {...this.state}
        filter={filter}
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
