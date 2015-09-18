import React from 'react';
import { List } from './List';
import { connect } from 'react-redux';
import { sortingDataSelector } from '../../Selectors/list';

@connect(state => {
  return ({
    elements: state.Feedback.nav.get('feedback'),
    viewModeOptions: state.Feedback.nav.get('viewModeOptions'),
    currentSortMode: sortingDataSelector(state)
  });
})

export class ListContainer extends React.Component {
  render() {

    return (
      <List {...this.props} />
    );
  }
}