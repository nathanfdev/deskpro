import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { CollectionField } from '../../../../Common/Components/Popup/index';
import { AgentTeamsList } from './AgentTeamsList';

@connect(state => ({
  values: allSelectorFactory('AgentTeam')(state)
}))
export class TeamsListContainer extends Component {

  render() {
    return (
      <CollectionField title="Team">
        <AgentTeamsList param="team" {...this.props} />
      </CollectionField>
    );
  }
}
