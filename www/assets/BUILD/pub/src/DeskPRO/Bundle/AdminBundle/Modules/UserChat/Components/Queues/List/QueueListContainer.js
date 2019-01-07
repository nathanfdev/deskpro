import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import QueueList from './QueueList';
import { allAgentsSelector, allAgentTeamsSelector, isAgentsLoadedSelector, isAgentTeamsLoadedSelector } from '../../../../Application/Selectors/people';
import { allQueuesSelector, isQueuesLoadedSelector } from '../../../Selectors/queue';
import { loadQueues, loadQueueSettings, updateQueueSettings } from '../../../Actions/queueActions';
import { loadAgents, loadAgentTeams } from '../../../../Application/Actions/peopleActions';
import LoadingPage from '../../../../Common/Components/LoadingPage';
import { replaceRoute } from '../../../../../Services/history';

@connect(state => ({
  agents:           allAgentsSelector(state),
  agentsLoaded:     isAgentsLoadedSelector(state),
  agentTeams:       allAgentTeamsSelector(state),
  agentTeamsLoaded: isAgentTeamsLoadedSelector(state),
  queues:           allQueuesSelector(state),
  queuesLoaded:     isQueuesLoadedSelector(state)
}))
class QueueListContainer extends React.Component {

  static propTypes = {
    queuesLoaded:     PropTypes.bool,
    agentsLoaded:     PropTypes.bool,
    agentTeamsLoaded: PropTypes.bool,
    dispatch:         PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      defaultQueue:   null,
      maxChatsCount:  null,
      settingsLoaded: false
    };
  }

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAgents());
    dispatch(loadQueues());
    dispatch(loadAgentTeams());

    const promise = dispatch(loadQueueSettings());
    promise.success(({ data }) => {
      this.setState({
        defaultQueue:   data.default_queue,
        maxChatsCount:  data.max_chats_count,
        settingsLoaded: true
      });
    });
  }

  addQueue = (event) => {
    event.preventDefault();
    replaceRoute('/chat/queues/new');
  };

  editQueue = (queue) => {
    replaceRoute(`/chat/queues/${queue.get('id')}`);
  };

  changeDefaultQueue = (value) => {
    this.setState({ defaultQueue: value });
    this.props.dispatch(updateQueueSettings({
      default_queue: value
    }));
  };

  changeMaxChatsCount = (value) => {
    let maxChatsCount = value;
    if (maxChatsCount < 1) {
      maxChatsCount = 1;
    }

    this.setState({ maxChatsCount });
    this.props.dispatch(updateQueueSettings({
      max_chats_count: maxChatsCount
    }));
  };

  render() {
    const { queuesLoaded, agentsLoaded, agentTeamsLoaded } = this.props;
    const { settingsLoaded } = this.state;

    if (!queuesLoaded || !agentsLoaded || !agentTeamsLoaded || !settingsLoaded) {
      return <LoadingPage />;
    }

    return (
      <QueueList
        {...this.props}
        {...this.state}
        addQueue={this.addQueue}
        editQueue={this.editQueue}
        changeDefaultQueue={this.changeDefaultQueue}
        changeMaxChatsCount={this.changeMaxChatsCount}
      />
    );
  }
}

export default QueueListContainer;
