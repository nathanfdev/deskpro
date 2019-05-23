import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import BaseQueueFormContainer from './BaseQueueFormContainer';
import { loadQueues } from '../../../Actions/queueActions';
import { allQueuesSelector, isQueuesLoadedSelector } from '../../../Selectors/queue';
import { isAccountsLoadedSelector, allAccountsSelector } from '../../../Selectors/account';
import { loadAccounts } from '../../../Actions/accountActions';
import { loadAgents, loadAgentTeams } from '../../../../Application/Actions/peopleActions';
import { loadSelectableTicketDepartments } from '../../../../Application/Actions/departmentsActions';
import { allAgentsSelector, isAgentsLoadedSelector, allAgentTeamsSelector, isAgentTeamsLoadedSelector } from '../../../../Application/Selectors/people';
import { selectableTicketDepartmentsSelector, isTicketDepartmentsLoadedSelector } from '../../../../Application/Selectors/departments';
import { allBrandsSelector, allBrandsLoadedSelector } from '../../../../Application/Selectors/brands';
import { loadBrands } from '../../../../Application/Actions/brandsActions';

@connect(state => ({
  agents:                  allAgentsSelector(state),
  agentsLoaded:            isAgentsLoadedSelector(state),
  queues:                  allQueuesSelector(state),
  queuesLoaded:            isQueuesLoadedSelector(state),
  accounts:                allAccountsSelector(state),
  accountsLoaded:          isAccountsLoadedSelector(state),
  agentTeams:              allAgentTeamsSelector(state),
  agentTeamsLoaded:        isAgentTeamsLoadedSelector(state),
  ticketDepartments:       selectableTicketDepartmentsSelector(state),
  ticketDepartmentsLoaded: isTicketDepartmentsLoadedSelector(state),
  brands:                  allBrandsSelector(state),
  brandsLoaded:            allBrandsLoadedSelector(state)
}))
class LoadingFormContainer extends BaseQueueFormContainer {

  static propTypes = {
    dispatch:                PropTypes.func,
    queuesLoaded:            PropTypes.bool,
    agentsLoaded:            PropTypes.bool,
    accountsLoaded:          PropTypes.bool,
    agentTeamsLoaded:        PropTypes.bool,
    ticketDepartmentsLoaded: PropTypes.bool,
    form:                    PropTypes.class
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAgents());
    dispatch(loadAgentTeams());
    dispatch(loadSelectableTicketDepartments(true));
    dispatch(loadAccounts());
    dispatch(loadQueues());
    dispatch(loadBrands(true));
  }

  render() {
    const { queuesLoaded, agentsLoaded, accountsLoaded, agentTeamsLoaded, ticketDepartmentsLoaded, brandsLoaded } = this.props;
    const { form } = this.props;

    if (!queuesLoaded || !agentsLoaded || !accountsLoaded || !agentTeamsLoaded || !ticketDepartmentsLoaded || !brandsLoaded) {
      return <LoadingPage />;
    }

    return React.createElement(form, { ...this.props });
  }
}

export default LoadingFormContainer;
