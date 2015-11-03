import React, { PropTypes } from 'react';
import {
  Header,
  Popup,
  FieldGroup,
  FloatField,
  CollectionField,
  QuickFilter,
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

  render() {
    const { agents, agentTeams, departments } = this.props;

    return (
      <Popup>
        <Header>Assign to Task</Header>

        <form>
          <div className="dpw--popup-content">
            <FieldGroup>
              <FloatField align="left">
                <QuickFilter value={this.state.quickFilter}
                             onChange={this.onChangeQuickFilter} />
              </FloatField>

              <FloatField align="right">
                <Unassign onClick={this.onUnassignAll} />
              </FloatField>
            </FieldGroup>

            <FieldGroup>
              <CollectionField>
                <div part="title">
                  Agent <a href="#" onClick={this.onAssignSelf}>Assign to me</a>
                </div>
                <AgentsList values={agents}
                            selected={this.state.agents}
                            showOnlySelected={this.state.showOnlySelected}
                            filter={this.state.quickFilter}
                            onChange={this.onChangeAgents} />
              </CollectionField>

              <CollectionField title="Team">
                <AgentTeamsList values={agentTeams}
                                selected={this.state.agentTeams}
                                showOnlySelected={this.state.showOnlySelected}
                                filter={this.state.quickFilter}
                                onChange={this.onChangeAgentTeams} />
              </CollectionField>

              <CollectionField title="Department">
                <DepartmentsList values={departments}
                                 selected={this.state.departments}
                                 showOnlySelected={this.state.showOnlySelected}
                                 filter={this.state.quickFilter}
                                 onChange={this.onChangeDepartments} />
              </CollectionField>
            </FieldGroup>
          </div>
        </form>
      </Popup>
    );
  }
}
