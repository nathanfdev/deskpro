import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import AvailableList from './AvailableList';
import { loadAvailableNumbers, addAvailableNumber, changeAvailableNumbersFilter } from '../../../../Actions/numberActions';
import { isAccountsLoadedSelector, allTwilioAccountsSelector } from '../../../../Selectors/account';
import { isNumbersLoadedSelector, availableNumbersFilterSelector } from '../../../../Selectors/numbers';
import BaseSearchContainer from '../BaseSearchContainer';
import { replaceRoute } from '../../../../../../Services/history';

@connect(state => ({
  accountsLoaded: isAccountsLoadedSelector(state),
  accounts:       allTwilioAccountsSelector(state),
  numbersLoaded:  isNumbersLoadedSelector(state),
  filter:         availableNumbersFilterSelector(state)
}))
class AvailableListContainer extends BaseSearchContainer {

  static propTypes = {
    accountsLoaded: PropTypes.bool,
    numbersLoaded:  PropTypes.bool,
    dispatch:       PropTypes.func,
    filter:         PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      loading: false,
      numbers: []
    };
  }

  onAddNumber = (number) => {
    this.setState({
      loading: true
    });

    const promise = this.props.dispatch(addAvailableNumber(number));
    promise.success(({ data }) => {
      this.setState({
        loading: false
      }, () => {
        const params = {
          sid:     data.sid,
          account: data.account,
          number:  data.number,
        };

        replaceRoute(`/voice_channel/numbers/new?${compileParams(params)}`);
      });
    });
    promise.error(() => {
      this.setState({
        loading: false
      });
    });
  };

  onChangeFilter = (filter) => {
    const { dispatch } = this.props;
    dispatch(changeAvailableNumbersFilter(filter));

    if (filter.account && filter.country_code && filter.types.length > 0) {
      this.setState({
        loading: true,
        numbers: []
      });

      dispatch(loadAvailableNumbers(filter.account, filter)).success((result) => {
        this.setState({
          loading: false,
          numbers: Immutable.fromJS(result.data)
        });
      });
    } else {
      this.setState({
        loading: false,
        numbers: []
      });
    }
  };

  render() {
    const { numbersLoaded, accountsLoaded, filter, accounts } = this.props;

    if (!numbersLoaded || !accountsLoaded) {
      return <LoadingPage />;
    }

    return (
      <AvailableList
        {...this.state}
        filter={filter}
        accounts={accounts}
        onClickBack={this.onClickBack}
        onChangeFilter={this.onChangeFilter}
        onAddNumber={this.onAddNumber}
      />
    );
  }
}

export default AvailableListContainer;
