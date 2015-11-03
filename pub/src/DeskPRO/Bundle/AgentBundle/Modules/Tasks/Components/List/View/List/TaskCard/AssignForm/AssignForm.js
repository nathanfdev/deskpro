import React, { PropTypes } from 'react';
import {
  Header,
  Popup,
  FieldGroup,
  FullField,
  FloatField,
  CollectionField,
  QuickFilter,
  ShowOnlySelected,
  Unassign,
  AgentsList,
  AgentTeamsList,
  DepartmentsList
} from '../../../../../Form/index';

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
      <Popup>
        <Header>Assign to task</Header>
      </Popup>
    );
  }
}
