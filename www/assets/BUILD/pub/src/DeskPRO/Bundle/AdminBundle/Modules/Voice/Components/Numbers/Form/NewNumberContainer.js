import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import NumberForm from './NumberForm';
import { createNumber } from '../../../Actions/numberActions';
import { loadAgents } from '../../../../Application/Actions/peopleActions';
import { loadQueues } from '../../../Actions/queueActions';
import { loadAutoAttendants } from '../../../Actions/autoAttendantActions';
import { isAgentsLoadedSelector } from '../../../../Application/Selectors/people';
import { isQueuesLoadedSelector } from '../../../Selectors/queue';
import { isAutoAttendantsLoadedSelector } from '../../../Selectors/autoAttendant';
import { replaceRoute } from '../../../../../Services/history';

@connect(state => ({
  queuesLoaded:         isQueuesLoadedSelector(state),
  agentsLoaded:         isAgentsLoadedSelector(state),
  autoAttendantsLoaded: isAutoAttendantsLoadedSelector(state)
}))
class NewNumberContainer extends React.Component {

  static propTypes = {
    dispatch:             PropTypes.func,
    location:             PropTypes.object,
    agentsLoaded:         PropTypes.bool,
    queuesLoaded:         PropTypes.bool,
    autoAttendantsLoaded: PropTypes.bool
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAgents());
    dispatch(loadQueues());
    dispatch(loadAutoAttendants());
  }

  onReturnBack = () => {
    replaceRoute('/voice_channel/numbers');
  };

  onSubmit = (data) => {
    const { location, dispatch } = this.props;

    const promise = dispatch(createNumber({ ...location.query, ...data }));
    promise.success(() => {
      replaceRoute('/voice_channel/numbers');
    });

    return promise;
  };

  render() {
    const { location, queuesLoaded, agentsLoaded, autoAttendantsLoaded } = this.props;
    const number = Immutable.fromJS(location.query);

    if (!queuesLoaded || !agentsLoaded || !autoAttendantsLoaded) {
      return <LoadingPage />;
    }

    return (
      <NumberForm
        {...this.state}
        number={number}
        onReturnBack={this.onReturnBack}
        onSubmit={this.onSubmit}
        onDelete={this.onDelete}
      />
    );
  }
}

export default NewNumberContainer;
