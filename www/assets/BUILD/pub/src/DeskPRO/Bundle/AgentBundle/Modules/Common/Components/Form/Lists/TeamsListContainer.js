import React, { Component, PropTypes } from 'react';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { paramsSelector } from '../../../../Application/Selectors/massActions';
import { CollectionField } from '../../../../Common/Components/Popup';
import { AgentTeamsList } from './AgentTeamsList';

import { connect } from 'react-redux';
@connect(state => ({
  teams: allSelectorFactory('AgentTeam')(state),
  currentParams: paramsSelector(state)
}))

export class TeamsListContainer extends Component {
  static propTypes = {
    onChange: PropTypes.func.isRequired,
    teams: PropTypes.object.isRequired,
    currentParams: PropTypes.object
  };

  render() {
    const { currentParams, teams, onChange } = this.props;
    const assign = currentParams.get('assign');

    return (
      <CollectionField title="Team">
        <AgentTeamsList values={teams}
                        selected={assign && assign.get('team')}
                        onChange={onChange}/>
      </CollectionField>
    );
  }
}