import React, { Component, PropTypes } from 'react';
import { paramsSelector } from '../../../../Application/Selectors/massActions';
import {
  AgentsList,
  AgentTeamsList,
  CollectionField,
  DepartmentsList,
  FieldGroup,
  Popup
} from '../../../../Tasks/Components/Form';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

import { connect } from 'react-redux';
@connect(state => ({
  agents: agentsSelector(state),
  agentTeams: allSelectorFactory('AgentTeam')(state),
  departments: allSelectorFactory('Department')(state),
  currentParams: paramsSelector(state)
}))

export class AssignActionContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    setParams: PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    agents: PropTypes.object.isRequired,
    agentTeams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    currentParams: PropTypes.object
  };

  onChange(param, value) {
    const { setParams, dispatch, currentParams } = this.props;
    if (currentParams && currentParams.get('assign') && currentParams.get('assign').get(param) && value.length < 1) {
      const nextParams = currentParams.get('assign').toJS();
      delete nextParams[param];
      dispatch(setParams({ assign: nextParams }));
    } else {
      dispatch(setParams({ assign: { [param]: value } }));
    }
  }

  render() {
    const { agents, agentTeams, departments, currentParams } = this.props;
    return (
      <div className="dpw-navigation-dropdown-panel">
        <Popup additionalClassNames="assign-form">
          <form>
            <div className="dpw--popup-content">
              <div className="dpw-departments-long-list">
                <div className="dpw--popup-item-collection">
                  <FieldGroup>
                    <CollectionField>
                      <div part="title">
                        Agent <a href="#" onClick={this.onAssignSelf}>Assign to me</a>
                      </div>
                      <AgentsList values={agents}
                                  selected={currentParams.get('assign') && currentParams.get('assign').get('agent')}
                                  onChange={this.onChange.bind(this, 'agent')}/>
                    </CollectionField>

                    <CollectionField title="Team">
                      <AgentTeamsList values={agentTeams}
                                      selected={currentParams.get('assign') && currentParams.get('assign').get('team')}
                                      onChange={this.onChange.bind(this, 'team')}/>
                    </CollectionField>

                    <CollectionField title="Department">
                      <DepartmentsList values={departments}
                                       selected={currentParams.get('assign') && currentParams.get('assign').get('department')}
                                       onChange={this.onChange.bind(this, 'department')}/>
                    </CollectionField>
                  </FieldGroup>
                </div>
              </div>
            </div>
          </form>
        </Popup>
      </div>
    );
  }
}