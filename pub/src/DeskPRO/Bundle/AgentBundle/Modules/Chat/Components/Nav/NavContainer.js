import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { pureRender } from 'Ampliflux';
import { DatePeriods } from 'DeskPRO/Bundle/AgentBundle/Services/DatePeriods';
import { agentNamesSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { reduceMapToProperty } from 'DeskPRO/Component/Util/Map';
import * as actions from '../../Actions/chatNavActions';
import * as listActions from '../../Actions/chatListActions';
import {loadedSelector, myChatsSelector, allChatsSelector } from '../../Selectors/nav';
import { Nav } from './Nav';
import { createDepartmentsRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';
const chatNavDepartmentsSelector = createDepartmentsRequestSelectors('chatNav');

@connect(state => ({
  loaded: loadedSelector(state),
  my: myChatsSelector(state),
  all: allChatsSelector(state),
  dpWindow: state.Application.dpWindow,
  labels: {
    agent: agentNamesSelector(state),
    department: reduceMapToProperty('title', chatNavDepartmentsSelector.recordsSel(state).toJS()),
    date_period: Immutable.fromJS(DatePeriods.all)
  }
}))
@pureRender
export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    currentApp: PropTypes.string.isRequired,
    my: PropTypes.object.isRequired,
    all: PropTypes.object.isRequired,
    loaded: PropTypes.bool.isRequired,
    labels: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired
  };

  componentDidMount() {
    this.props.dispatch(actions.initialLoad());
  }

  componentWillUnmount() {
    this.props.dispatch(actions.unmount());
  }

  toggleGroupingVisibility(listName) {
    return (e) => {
      e.preventDefault();
      this.props.dispatch(actions.toggleListGroupingVisibility(listName));
    };
  }

  changeGrouping(listName) {
    return (e) => {
      const options = e.target.options;
      for (let i = 0; i < options.length; i++) {
        if (options[i].selected) {
          this.props.dispatch(actions.changeListGrouping(listName, options[i].value));
        }
      }
    };
  }

  render() {
    const { my, all, labels, dpWindow, dispatch, loaded } = this.props;
    const changeGrouping = (listName) => this.changeGrouping(listName).bind(this);
    const toggleGroupingVisibility = (listName) => this.toggleGroupingVisibility(listName).bind(this);
    const onMyClick = (filters) => dispatch(listActions.load({ ...filters, agent: 'me' }));
    const onAllClick = (filters) => dispatch(listActions.load(filters));

    return (
      <Nav my={my}
           all={all}
           labels={labels}
           onMyClick={onMyClick}
           onAllClick={onAllClick}
           changeGrouping={changeGrouping}
           toggleGroupingVisibility={toggleGroupingVisibility}
           dpWindow={dpWindow}
           dispatch={dispatch}
           loaded={loaded}/>
    );
  }
}
