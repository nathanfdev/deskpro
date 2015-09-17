import React from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/chatNavActions';
import * as listActions from '../../Actions/chatListActions';
import { Nav } from '../../Components/Nav/Nav';

@connect(state => {
  return ({
    lists: state.Chat.nav.get('lists').toJS(),
    grouping: {
      my: {
        visible: state.Chat.nav.getIn(['lists', 'my', 'isGroupingControlVisible']),
        options: [
          {value: 'date_period', label: 'Date Created'},
          {value: 'department', label: 'Department'}
        ]
      },
      all: {
        visible: state.Chat.nav.getIn(['lists', 'all', 'isGroupingControlVisible']),
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
    const { lists, grouping } = this.props;
    const changeGrouping = (listName) => this.changeGrouping(listName).bind(this);
    const toggleGroupingVisibility = (listName) => this.toggleGroupingVisibility(listName).bind(this);
    const onMyClick = (filters) => this.props.dispatch(listActions.load({...filters, agent: 'me'}));
    const onAllClick = (filters) => this.props.dispatch(listActions.load(filters));

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

  componentWillUnmount() {
    this.props.dispatch(actions.unmount());
  }
}
