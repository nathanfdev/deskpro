import React from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/chatNavActions';
import * as listActions from '../../Actions/chatListActions';
import { Nav } from './Nav';
import { pureRender } from 'Ampliflux';

@connect(state => ({
  lists: state.Chat.nav.get('lists'),
  dpWindow: state.Application.dpWindow
}))
@pureRender
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
    this.props.dispatch(actions.loadCounts('my', this.props.lists.getIn(['my', 'groupBy'])));
    this.props.dispatch(actions.loadCounts('all', this.props.lists.getIn(['all', 'groupBy'])));
  }

  render() {
    const { lists, dpWindow, dispatch } = this.props;
    const changeGrouping = (listName) => this.changeGrouping(listName).bind(this);
    const toggleGroupingVisibility = (listName) => this.toggleGroupingVisibility(listName).bind(this);
    const onMyClick = (filters) => this.props.dispatch(listActions.load({...filters, agent: 'me'}));
    const onAllClick = (filters) => this.props.dispatch(listActions.load(filters));

    return (
      <Nav
        lists={lists}
        onMyClick={onMyClick}
        onAllClick={onAllClick}
        changeGrouping={changeGrouping}
        toggleGroupingVisibility={toggleGroupingVisibility}
        dpWindow={dpWindow}
        dispatch={dispatch}
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
