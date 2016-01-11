import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { pureRender } from 'Ampliflux';
import * as actions from '../../Actions/chatNavActions';
import {loadedSelector, myChatsSelector, allChatsSelector } from '../../Selectors/nav';
import { Nav } from './Nav';
import { createDepartmentsRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';

@connect(state => ({
  loaded: loadedSelector(state),
  my: myChatsSelector(state),
  all: allChatsSelector(state),
  dpWindow: state.Application.dpWindow
}))
@pureRender
export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    currentApp: PropTypes.string.isRequired,
    my: PropTypes.object.isRequired,
    all: PropTypes.object.isRequired,
    loaded: PropTypes.bool.isRequired,
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
    const { my, all, dpWindow, dispatch, loaded } = this.props;
    const changeGrouping = (listName) => this.changeGrouping(listName).bind(this);
    const toggleGroupingVisibility = (listName) => this.toggleGroupingVisibility(listName).bind(this);

    return (
      <Nav my={my}
           all={all}
           changeGrouping={changeGrouping}
           toggleGroupingVisibility={toggleGroupingVisibility}
           dpWindow={dpWindow}
           dispatch={dispatch}
           loaded={loaded}/>
    );
  }
}
