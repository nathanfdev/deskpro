import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import AvailableList from './AvailableList';
import { loadAvailableNumbers, addAvailableNumber } from '../../../../Actions/numberActions';
import { isAccountsLoadedSelector, allAccountsSelector } from '../../../../Selectors/account';
import { isNumbersLoadedSelector } from '../../../../Selectors/numbers';
import BaseSearchContainer from '../BaseSearchContainer';
import { replaceRoute } from '../../../../../../Services/history';

@connect(state => ({
  accountsLoaded: isAccountsLoadedSelector(state),
  accounts:       allAccountsSelector(state),
  numbersLoaded:  isNumbersLoadedSelector(state)
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
      numbers: [],
      filter:  {
        account:      null,
        country_code: null,
        region:       null,
        types:        ['local', 'tollfree', 'mobile', 'fixed', 'national'],
        phrase:       ''
      }
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
    const { dispatch, accounts } = this.props;
    if (filter.account && filter.country_code && filter.types.length > 0) {
      this.setState({
        loading: true,
        numbers: []
      });

      const account = accounts.get(filter.account);
      if (account) {
        dispatch(loadAvailableNumbers(account, filter)).success((result) => {
          this.setState({
            loading: false,
            numbers: Immutable.fromJS(result.data),
            filter
          });
        });
      }
    } else {
      this.setState({
        loading: false,
        numbers: [],
        filter
      });
    }
  };

  render() {
    const { numbersLoaded, accountsLoaded, accounts } = this.props;

    if (!numbersLoaded || !accountsLoaded) {
      return <LoadingPage />;
    }

    return (
      <AvailableList
        {...this.state}
        accounts={accounts}
        onClickBack={this.onClickBack}
        onChangeFilter={this.onChangeFilter}
        onAddNumber={this.onAddNumber}
      />
    );
  }
}

export default AvailableListContainer;
