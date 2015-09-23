import React from 'react';
import { connect } from 'react-redux';
import { elementsSelector, viewModeSelector } from '../../Selectors/list';
import { List } from './List';

@connect(state => ({
  elements: elementsSelector(state),
  viewMode: viewModeSelector(state)
}))
export class ListContainer extends React.Component {
  render() {
    return (
      <List {...this.props} />
    );
  }
}
