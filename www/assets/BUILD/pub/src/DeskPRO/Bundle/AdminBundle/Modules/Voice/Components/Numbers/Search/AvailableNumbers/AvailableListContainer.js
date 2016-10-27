import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import Modal from 'DeskPRO/Component/Semantic/Modal';
import AvailableList from './AvailableList';
import { loadAvailableNumbers, addAvailableNumber, changeAvailableNumbersFilter } from '../../../../Actions/numberActions';
import { isAccountsLoadedSelector, allAccountsSelector } from '../../../../Selectors/account';
import { isNumbersLoadedSelector, availableNumbersFilterSelector } from '../../../../Selectors/numbers';
import BaseSearchContainer from '../BaseSearchContainer';
import { replaceRoute } from '../../../../../../Services/history';

@connect(state => ({
  accountsLoaded: isAccountsLoadedSelector(state),
  accounts:       allAccountsSelector(state),
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
      loading: true,
      error:   null
    });

    const promise = this.props.dispatch(addAvailableNumber(number));
    promise.success(() => {
      this.setState({
        loading: false
      }, () => replaceRoute('/voice_channel/numbers'));
    });
    promise.error((response) => {
      this.setState({
        loading: false,
        error:   response.message
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

  onCloseModal = () => {
    this.setState({
      error: null
    });
  };

  render() {
    const { numbersLoaded, accountsLoaded, filter, accounts } = this.props;
    const { error } = this.state;

    if (!numbersLoaded || !accountsLoaded) {
      return <LoadingPage />;
    }

    return (
      <div>
        <AvailableList
          {...this.state}
          filter={filter}
          accounts={accounts}
          onClickBack={this.onClickBack}
          onChangeFilter={this.onChangeFilter}
          onAddNumber={this.onAddNumber}
        />
        <Modal isOpen={error} onClose={this.onCloseModal}>
          {error}
        </Modal>
      </div>
    );
  }
}

export default AvailableListContainer;
