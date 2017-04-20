import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import NumberForm from './NumberForm';
import { loadNumbers, editNumber } from '../../../Actions/numberActions';
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

  onReturnBack = () => {
    replaceRoute('/voice_channel/numbers');
  };

  onSubmit = (data) => {
    const { dispatch } = this.props;
    const number = this.getNumber();

    this.setState({
      saving: true,
      errors: {}
    });

    const promise = dispatch(editNumber(number.get('id'), data));
    promise.success(() => {
      replaceRoute('/voice_channel/numbers');
    });
    promise.error((result) => {
      this.setState({
        errors: result.errors,
        saving: false
      });
    });
  };

  getNumber() {
    const { params, numbers } = this.props;
    const numberId = parseInt(params.numberId, 10);

    return numbers && numbers.get(numberId);
  }

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
        onReturnBack={this.onReturnBack}
        onSubmit={this.onSubmit}
      />
    );
  }
}

export default EditNumberContainer;
