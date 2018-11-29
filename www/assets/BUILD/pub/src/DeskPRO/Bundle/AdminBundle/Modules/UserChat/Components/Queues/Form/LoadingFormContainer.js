import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import QueueForm from './QueueForm';
import {
  isAgentsLoadedSelector,
  isAgentTeamsLoadedSelector,
  isAgentGroupsLoadedSelector,
  allAgentTeamsSelector,
  allAgentsSelector,
  allAgentGroupsSelector
} from '../../../../Application/Selectors/people';
import {
  loadAgents,
  loadAgentTeams,
  loadAgentGroups,
  loadDepartmentAgents,
  loadGroupAgents,
  loadTeamAgents
} from '../../../../Application/Actions/peopleActions';
import { loadSelectableChatDepartments } from '../../../../Application/Actions/departmentsActions';
import { loadQueues } from '../../../Actions/queueActions';
import { allQueuesSelector, isQueuesLoadedSelector } from '../../../Selectors/queue';
import LoadingPage from '../../../../Common/Components/LoadingPage';
import {
  isChatDepartmentsLoadedSelector,
  selectableChatDepartmentsSelector
} from '../../../../Application/Selectors/departments';
import { replaceRoute } from '../../../../../Services/history';

@connect(state => ({
  agentsLoaded:          isAgentsLoadedSelector(state),
  agentTeamsLoaded:      isAgentTeamsLoadedSelector(state),
  agentGroupsLoaded:     isAgentGroupsLoadedSelector(state),
  chatDepartmentsLoaded: isChatDepartmentsLoadedSelector(state),
  queues:                allQueuesSelector(state),
  queuesLoaded:          isQueuesLoadedSelector(state),
  agents:                allAgentsSelector(state),
  agentTeams:            allAgentTeamsSelector(state),
  agentGroups:           allAgentGroupsSelector(state),
  chatDepartments:       selectableChatDepartmentsSelector(state)
}))
class LoadingFormContainer extends React.Component {

  static propTypes = {
    agentsLoaded:          PropTypes.bool,
    agentTeamsLoaded:      PropTypes.bool,
    agentGroupsLoaded:     PropTypes.bool,
    chatDepartmentsLoaded: PropTypes.bool,
    queuesLoaded:          PropTypes.bool,
    dispatch:              PropTypes.func
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAgents());
    dispatch(loadAgentTeams());
    dispatch(loadAgentGroups());
    dispatch(loadSelectableChatDepartments());
    dispatch(loadQueues());
  }

  loadTeamAgentsList = id => this.props.dispatch(loadTeamAgents(id));
  loadDepartmentAgentsList = id => this.props.dispatch(loadDepartmentAgents(id));
  loadGroupAgentsList = id => this.props.dispatch(loadGroupAgents(id));

  returnBack = () => {
    replaceRoute('/chat/queues');
  };

  render() {
    const { agentsLoaded, agentTeamsLoaded, agentGroupsLoaded, chatDepartmentsLoaded, queuesLoaded } = this.props;

    if (!agentsLoaded || !agentTeamsLoaded || !agentGroupsLoaded || !queuesLoaded || !chatDepartmentsLoaded) {
      return <LoadingPage />;
    }

    return (
      <QueueForm
        {...this.props}
        returnBack={this.returnBack}
        loadTeamAgentsList={this.loadTeamAgentsList}
        loadDepartmentAgentsList={this.loadDepartmentAgentsList}
        loadGroupAgentsList={this.loadGroupAgentsList}
      />
    );
  }
}

export default LoadingFormContainer;
