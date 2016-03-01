import React, { PropTypes } from 'react';

export class BaseForm extends React.Component {

  static propTypes = {
    me: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      quickFilter: '',
      agents: [],
      agentTeams: [],
      departments: [],
      errors: {},
      submit: false
    };
  }

  onChangeQuickFilter = value => {
    this.setState({
      quickFilter: value
    });
  };

  onAssignSelf = () => {
    const id = this.props.me.get('id');
    const selected = this.state.agents;
    if (id && selected.indexOf(id) === -1) {
      selected.push(id);
    }

    this.setState({
      agents: selected
    });
  };

  onUnassignAll = (event) => {
    event.preventDefault();
    this.setState({
      agents: [],
      agentTeams: [],
      departments: []
    });
  };

  onChangeAgents = selected => {
    this.setState({
      agents: selected
    });
  };

  onChangeAgentTeams = selected => {
    this.setState({
      agentTeams: selected
    });
  };

  onChangeDepartments = selected => {
    this.setState({
      departments: selected
    });
  };
}
