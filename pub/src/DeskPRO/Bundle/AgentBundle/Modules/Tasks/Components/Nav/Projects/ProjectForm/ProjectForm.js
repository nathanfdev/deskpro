import React, { PropTypes } from 'react';
import * as TasksActions from '../../../../Actions/tasksActions';
import { Header } from './Header';
import { FieldGroup } from './Fields/FieldGroup';
import { FullField } from './Fields/FullField';
import { FloatField } from './Fields/FloatField';
import { CollectionField } from './Fields/CollectionField';
import { QuickFilter } from './Fields/QuickFilter';
import { ShowOnlySelected } from './Fields/ShowOnlySelected';
import { Unassign } from './Fields/Unassign';
import { AgentsList } from './Fields/AgentsList';
import { AgentTeamsList } from './Fields/AgentTeamsList';
import { DepartmentsList } from './Fields/DepartmentsList';

export class ProjectForm extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    me: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    agentTeams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      title: '',
      quickFilter: '',
      showOnlySelected: false,
      agents: [],
      agentTeams: [],
      departments: []
    };
  }

  onChangeTitle = event => {
    this.setState({
      title: event.target.value
    });
  };

  onChangeQuickFilter = value => {
    this.setState({
      quickFilter: value
    });
  };

  onChangeFilterSelected = value => {
    this.setState({
      showOnlySelected: value
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

  onUnassignAll = () => {
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

  onSubmit = event => {
    event.preventDefault();

    TasksActions.createProject({
      title: this.state.title,
      departments: this.state.departments,
      teams: this.state.agentTeams,
      agents: this.state.agents
    });
  };

  render() {
    const { agents, agentTeams, departments } = this.props;

    return (
      <div className="sidebar-hover">
        <div className="dpw--popup-main">
          <Header>Project - Create New</Header>

          <form>
            <div className="dpw--popup-content">
              <FieldGroup>
                <FullField title="Title">
                  <input name="title"
                         type="text"
                         placeholder="Title"
                         value={this.state.title}
                         onChange={this.onChangeTitle} />
                </FullField>
              </FieldGroup>

              <FieldGroup>
                <FloatField align="left">
                  <QuickFilter value={this.state.quickFilter}
                               onChange={this.onChangeQuickFilter} />
                </FloatField>

                <FloatField align="right">
                  <ShowOnlySelected value={this.state.showOnlySelected}
                                    onChange={this.onChangeFilterSelected} />
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

              <FieldGroup>
                <FullField>
                  <button type="submit"
                          value="Save"
                          className="dpw--popup-button"
                          onClick={this.onSubmit}>Save</button>
                </FullField>
              </FieldGroup>
            </div>
          </form>
        </div>
      </div>
    );
  }
}
