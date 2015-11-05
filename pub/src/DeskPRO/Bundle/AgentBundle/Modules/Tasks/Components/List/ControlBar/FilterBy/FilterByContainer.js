import React from 'react';
import { connect } from 'react-redux';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import { FilterBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { FilterByForm } from './FilterByForm';
import { allProjectsSelector } from '../../../../RecordStores/Selectors/projectSelectors';
import { allTaskLabelsSelector } from '../../../../RecordStores/Selectors/taskLabelSelectors';
import { loadAllTaskLabels } from '../../../../RecordStores/Actions/taskLabelActions';
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { agentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { allDepartmentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';
import { loadAllProjects } from '../../../../RecordStores/Actions/projectActions';

@connect(state => ({
  projects: allProjectsSelector(state),
  labels: allTaskLabelsSelector(state),
  agents: agentsSelector(state),
  agentTeams: agentTeamsSelector(state),
  departments: allDepartmentsSelector(state)
}))
export class FilterByContainer extends React.Component {

  constructor(props) {
    super(props);

    props.dispatch(loadAllProjects());
    props.dispatch(loadAllTaskLabels());

    this.state = {
      dropdownOpened: false
    };
  }

  onOpenDropdown = () => {
    this.setState({
      dropdownOpened: true
    });
  };

  onCloseDropDown = () => {
    this.setState({
      dropdownOpened: false
    });
  };

  render() {
    return (
      <FilterBy ref="button"
                title="Filter by:"
                toggleDropdown={this.onOpenDropdown}>

        <Detached isOpen={this.state.dropdownOpened}
                  positionAt="left bottom+8"
                  positionTarget={this.refs.button}>

          <ClickOut onClickOut={this.onCloseDropDown}>
            <FilterByForm {...this.props} />
          </ClickOut>
        </Detached>

      </FilterBy>
    );
  }
}
