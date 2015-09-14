import React from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/chatNavActions';
import * as listActions from '../../Actions/chatListActions';
import { Nav } from './Nav';

/**
 * Todo: Unlike vanilla Redux, Ampliflux's createAction has no access to the state, thus we need to select needed data
 *       in components, pass it through components hierarchy and pass as parameters to action creators
 *
 * Todo: Make possible to access state in actionCreate. Remove sort & order from this component.
 */
@connect(state => {
  return ({
    sort: state.Chat.list.get('sort'),
    query: state.Chat.list.get('query'),
    order: state.Chat.list.get('order'),
    lists: state.Chat.nav.get('lists').toJS(),
    grouping: {
      my: {
        visible: state.Chat.nav.get('lists').get('my').isGroupingControlVisible,
        options: [
          {value: 'date_period', label: 'Date Created'},
          {value: 'department', label: 'Department'}
        ]
      },
      all: {
        visible: state.Chat.nav.get('lists').get('all').isGroupingControlVisible,
        options: [
          {value: 'agent', label: 'Agent'},
          {value: 'department', label: 'Department'},
          {value: 'date_period', label: 'Date Created'}
        ]
      }
    }
  })
})
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
    this.props.dispatch(actions.loadCounts('my', this.props.lists.my.groupBy));
    this.props.dispatch(actions.loadCounts('all', this.props.lists.all.groupBy));
  }

  render() {
    const { lists, grouping, sort, order } = this.props;
    const changeGrouping = (listName) => this.changeGrouping(listName).bind(this);
    const toggleGroupingVisibility = (listName) => this.toggleGroupingVisibility(listName).bind(this);
    const onMyClick = (filters) => {
      this.props.dispatch(listActions.load({...filters, agent: 'me', sort: sort, order: order}))
    };
    const onAllClick = (filters) => {
      this.props.dispatch(listActions.load({...filters, sort: sort, order: order}))
    };

    return (
      <Nav
        lists={lists}
        grouping={grouping}
        onMyClick={onMyClick}
        onAllClick={onAllClick}
        changeGrouping={changeGrouping}
        toggleGroupingVisibility={toggleGroupingVisibility}
      />
    );
  }

  toggleGroupingVisibility(listName) {
    return function(e) {
      e.preventDefault();
      this.props.dispatch(actions.toggleListGroupingVisibility(listName));
    };
  }

  changeGrouping(listName) {
    return function (e) {
      const options = e.target.options;
      for (let i = 0; i < options.length; i++) {
        if (options[i].selected) {
          this.props.dispatch(actions.changeListGrouping(listName, options[i].value));
        }
      }
    }
  }
}
