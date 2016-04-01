import React, { Component, PropTypes } from 'react';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { CollectionField } from '../../../../Common/Components/Popup';
import { AgentTeamsList } from './AgentTeamsList';

import { connect } from 'react-redux';
@connect(state => ({
  teams: allSelectorFactory('AgentTeam')(state)
}))

export class TeamsListContainer extends Component {
  static propTypes = {
    onClick: PropTypes.func.isRequired,
    teams: PropTypes.object.isRequired,
    multiple: PropTypes.bool,
    showOnlySelected: PropTypes.bool,
    filter: PropTypes.string,
    selected: PropTypes.number
  };

  render() {
    const { selected, teams, onClick, filter, multiple, showOnlySelected } = this.props;

    return (
      <CollectionField title="Team">
        <AgentTeamsList param="team"
                        values={teams}
                        filter={filter}
                        selected={selected}
                        multiple={multiple}
                        showOnlySelected={showOnlySelected}
                        onClick={onClick}/>
      </CollectionField>
    );
  }
}