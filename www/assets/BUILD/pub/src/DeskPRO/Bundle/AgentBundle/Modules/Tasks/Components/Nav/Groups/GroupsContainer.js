import React from 'react';
import { connect } from 'react-redux';
import { Groups } from './Groups';
import { groupsCountMapSelector } from '../../../Selectors/nav';
import { pureRender } from 'Ampliflux';

@connect(state => ({
  groupsCountMap: groupsCountMapSelector(state)
}))

@pureRender

export class GroupsContainer extends React.Component {

  render() {
    return <Groups {...this.props} />;
  }
}
