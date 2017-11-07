import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { Container, Button, Checkbox } from '@deskpro/react-components';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { ActionsBlock } from './ActionsBlock';
import { FollowUpTime } from './FollowUpTime';

@connect(state => ({
  agents:     agentsSelector(state),
  agentTeams: allSelectorFactory('AgentTeam')(state)
}))
export class FollowUpContainer extends React.Component {
  render() {
    const props = this.props;
    return (
      <FollowUp
        {...props}
      />
    );
  }
}

export class FollowUp extends React.Component {
  static propTypes = {
    agents:     PropTypes.object.isRequired,
    agentTeams: PropTypes.object.isRequired,
  };

  constructor(props) {
    super(props);

    this.state = {
      actions:       [],
      cancelIfReply: false,
    };
  }

  updateActions = (actions) => {
    this.setState({
      actions
    });
  };

  updateCancelIfReply = (cancelIfReply) => {
    this.setState({
      cancelIfReply
    });
  };

  render() {
    return (
      <Container className="follow_up">
        <h4>Add Follow Up</h4>
        <h5>Follow Up Time</h5>
        <FollowUpTime />
        <h5>Follow Up Actions</h5>
        <ActionsBlock
          actions={this.state.actions}
          onChange={this.updateActions}
          agents={this.props.agents}
          agentTeams={this.props.agentTeams}
        />
        <h5>Criteria</h5>
        <Checkbox
          checked={this.state.cancelIfReply}
          onChange={this.updateCancelIfReply}
        >
          Cancel follow up if user replies
        </Checkbox>
        <Button size="medium">Create</Button>
      </Container>
    );
  }
}
