import React from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
import QueueForm from './QueueForm';
import { allAgentsSelector, isAgentsLoadedSelector, allAgentTeamsSelector, isAgentTeamsLoadedSelector } from '../../../../Application/Selectors/people';
import { loadAgents, loadAgentTeams } from '../../../../Application/Actions/peopleActions';
import LoadingPage from '../../../../Common/Components/LoadingPage';
import { replaceRoute } from '../../../../../Services/history';
import { createQueue } from '../../../Actions/queueActions';

@connect(state => ({
  agents:           allAgentsSelector(state),
  agentsLoaded:     isAgentsLoadedSelector(state),
  agentTeams:       allAgentTeamsSelector(state),
  agentTeamsLoaded: isAgentTeamsLoadedSelector(state)
}))
class NewQueueContainer extends React.Component {

  static propTypes = {
    agentsLoaded:     PropTypes.bool,
    agentTeamsLoaded: PropTypes.bool,
    dispatch:         PropTypes.func
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAgents());
    dispatch(loadAgentTeams());
  }

  returnBack = () => {
    replaceRoute('/chat/queues');
  };

  submitData = (data) => {
    const promise = this.props.dispatch(createQueue(data));
    promise.success(() => {
      replaceRoute('/chat/queues');
    });

    return promise;
  };

  render() {
    const { agentsLoaded, agentTeamsLoaded } = this.props;

    if (!agentsLoaded || !agentTeamsLoaded) {
      return <LoadingPage />;
    }

    return (
      <QueueForm
        {...this.props}
        returnBack={this.returnBack}
        onSubmit={this.submitData}
      />
    );
  }
}

export default NewQueueContainer;
