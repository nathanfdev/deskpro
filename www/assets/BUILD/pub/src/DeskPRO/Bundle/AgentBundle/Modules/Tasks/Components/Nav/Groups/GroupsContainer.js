import React from 'react';
import { connect } from 'react-redux';
import { pureRender } from 'DeskPRO/Component/Ampliflux';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { Groups } from './Groups';
import { groupsCountMapSelector } from '../../../Selectors/nav';

@connect(state => ({
  myTeams:        collectionSelectorFactory('AgentTeam', 'my')(state),
  groupsCountMap: groupsCountMapSelector(state)
}))

@pureRender

export class GroupsContainer extends React.Component {

  render() {
    return <Groups {...this.props} />;
  }
}
