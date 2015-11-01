import React, { PropTypes } from 'react';
import { Header } from './Header';
import { FieldGroup } from './Fields/FieldGroup';
import { FullField } from './Fields/FullField';
import { FloatField } from './Fields/FloatField';
import { CollectionField } from './Fields/CollectionField';
import { QuickFilter } from './Fields/QuickFilter';
import { ShowOnlySelected } from './Fields/ShowOnlySelected';
import { Unassign } from './Fields/Unassign';
import { CheckboxList } from './Fields/CheckboxList';

export class ProjectForm extends React.Component {

  static propTypes = {
    agents: PropTypes.object.isRequired,
    agentTeams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      title: '',
      quick_filter: '',
      filter_selected: false,
      agents: ['val2'],
      agent_teams: [],
      departments: ['val1']
    };
  }

  onChangeTitle = event => {
    this.setState({
      title: event.target.value
    });
  };

  onChangeQuickFilter = value => {
    this.setState({
      quick_filter: value
    });
  };

  onChangeFilterSelected = value => {
    this.setState({
      filter_selected: value
    });
  };

  onAssignSelf = () => {
    console.log('onAssignSelf');
  };

  onUnassignAll = () => {
    console.log('onUnassignAll');
  };

  onChangeAgents = selected => {
    this.setState({
      agents: selected
    });
  };

  onChangeAgentTeams = selected => {
    this.setState({
      agent_teams: selected
    });
  };

  onChangeDepartments = selected => {
    this.setState({
      departments: selected
    });
  };

  onSubmit = event => {
    event.preventDefault();
  };

  render() {
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
                  <QuickFilter value={this.state.quick_filter}
                               onChange={this.onChangeQuickFilter} />
                </FloatField>

                <FloatField align="right">
                  <ShowOnlySelected value={this.state.filter_selected}
                                    onChange={this.onChangeFilterSelected} />
                  <Unassign onClick={this.onUnassignAll} />
                </FloatField>
              </FieldGroup>

              <FieldGroup>
                <CollectionField>
                  <div part="title">
                    Agent <a href="#" onClick={this.onAssignSelf}>Assign to me</a>
                  </div>
                  <CheckboxList options={[]}
                                selected={this.state.agents}
                                onChange={this.onChangeAgents} />
                </CollectionField>

                <CollectionField title="Team">
                  <CheckboxList options={[]}
                                selected={this.state.agent_teams}
                                onChange={this.onChangeAgentTeams} />
                </CollectionField>

                <CollectionField title="Department">
                  <CheckboxList options={[]}
                                selected={this.state.departments}
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
