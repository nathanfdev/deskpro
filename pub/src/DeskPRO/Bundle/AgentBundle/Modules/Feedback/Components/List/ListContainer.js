import React from 'react';
import { List } from './List';
import { connect } from 'react-redux';
import { viewDataSelector } from '../../Selectors/list';

@connect(state => {
  return ({
    elements: state.Feedback.nav.get('feedback').toJS(),
    currentViewMode: viewDataSelector(state)
  });
})

export class ListContainer extends React.Component {

  render() {
    const {elements, currentViewMode} = this.props;
console.log('Elements: ', elements);
    return (
      <List elements={elements} currentViewMode={currentViewMode}/>
    );
  }
}