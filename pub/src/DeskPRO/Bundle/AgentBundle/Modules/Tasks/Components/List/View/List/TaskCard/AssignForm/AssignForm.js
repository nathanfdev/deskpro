import React, { PropTypes } from 'react';

export class AssignForm extends React.Component {

  static propTypes = {
    project: PropTypes.object,
    dispatch: PropTypes.func.isRequired,
    me: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    agentTeams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired
  };

  render() {
    return (
      <div />
    );
  }
}
