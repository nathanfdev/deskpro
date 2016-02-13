import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { pureRender } from 'Ampliflux';
import * as actions from '../../Actions/chatNavActions';
import { isLoadedSelector, myChatsSelector, allChatsSelector } from '../../Selectors/nav';
import { Nav } from './Nav';

@connect(state => ({
  isLoaded: isLoadedSelector(state),
  my: myChatsSelector(state),
  all: allChatsSelector(state)
}))
@pureRender
export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
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
    const changeGrouping = (listName) => this.changeGrouping(listName).bind(this);
    const toggleGroupingVisibility = (listName) => this.toggleGroupingVisibility(listName).bind(this);

    return (
      <Nav {...this.props}
           changeGrouping={changeGrouping}
           toggleGroupingVisibility={toggleGroupingVisibility}/>
    );
  }
}
