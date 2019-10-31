import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import NumberForm from './NumberForm';
import { loadNumbers, editNumber, disableNumber, deleteNumber } from '../../../Actions/numberActions';
import { loadAgents } from '../../../../Application/Actions/peopleActions';
import { loadQueues } from '../../../Actions/queueActions';
import { loadAutoAttendants } from '../../../Actions/autoAttendantActions';
import { allNumbersSelector, isNumbersLoadedSelector } from '../../../Selectors/numbers';
import { isAgentsLoadedSelector } from '../../../../Application/Selectors/people';
import { isQueuesLoadedSelector } from '../../../Selectors/queue';
import { isAutoAttendantsLoadedSelector } from '../../../Selectors/autoAttendant';
import { replaceRoute } from '../../../../../Services/history';

@connect(state => ({
  numbers:              allNumbersSelector(state),
  numbersLoaded:        isNumbersLoadedSelector(state),
  queuesLoaded:         isQueuesLoadedSelector(state),
  agentsLoaded:         isAgentsLoadedSelector(state),
  autoAttendantsLoaded: isAutoAttendantsLoadedSelector(state)
}))
class EditNumberContainer extends React.Component {

  static propTypes = {
    dispatch:             PropTypes.func,
    numbers:              PropTypes.object,
    numbersLoaded:        PropTypes.bool,
    agentsLoaded:         PropTypes.bool,
    queuesLoaded:         PropTypes.bool,
    autoAttendantsLoaded: PropTypes.bool,
    params:               PropTypes.object
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadNumbers());
    dispatch(loadAgents());
    dispatch(loadQueues());
    dispatch(loadAutoAttendants());
  }

  onSubmit = (data) => {
    const { dispatch } = this.props;
    const number = this.getNumber();

    const promise = dispatch(editNumber(number.get('id'), data));
    promise.success(() => {
      replaceRoute('/voice_channel/numbers');
    });

    return promise;
  };

  getNumber() {
    const { params, numbers } = this.props;
    const numberId = parseInt(params.numberId, 10);

    return numbers && numbers.get(numberId);
  }

  disableNumber = () => {
    const { dispatch } = this.props;
    const number = this.getNumber();
    const promise = dispatch(disableNumber(number.get('id')));

    promise.success(() => {
      replaceRoute('/voice_channel/numbers');
    });

    return promise;
  };

  deleteNumber = () => {
    const { dispatch } = this.props;
    const number = this.getNumber();
    const promise = dispatch(deleteNumber(number.get('id')));

    promise.success(() => {
      replaceRoute('/voice_channel/numbers');
    });

    return promise;
  };

  returnBack = () => {
    replaceRoute('/voice_channel/numbers');
  };

  render() {
    const { numbersLoaded, queuesLoaded, agentsLoaded, autoAttendantsLoaded } = this.props;
    const number = this.getNumber();

    if (!number || !numbersLoaded || !queuesLoaded || !agentsLoaded || !autoAttendantsLoaded) {
      return <LoadingPage />;
    }

    return (
      <NumberForm
        {...this.state}
        number={number}
        returnBack={this.returnBack}
        onSubmit={this.onSubmit}
        disableNumber={this.disableNumber}
        deleteNumber={this.deleteNumber}
      />
    );
  }
}

export default EditNumberContainer;
