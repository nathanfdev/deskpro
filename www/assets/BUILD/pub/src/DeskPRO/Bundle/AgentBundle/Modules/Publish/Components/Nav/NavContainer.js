import React, { Component, PropTypes } from 'react';
import * as actions from '../../Actions/publishNavActions';
import { Nav } from './Nav';
import { connect } from 'react-redux';

@connect(state => ({
  isLoaded:  state.Publish.nav.getIn(['async', 'done']),
  articles:  state.Publish.nav.get('articles'),
  news:      state.Publish.nav.get('news'),
  downloads: state.Publish.nav.get('downloads'),
  todo:      state.Publish.nav.get('todo'),
  grouping:  state.Publish.nav.get('grouping')
}))

export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    grouping: PropTypes.object.isRequired
  };

  componentDidMount = () => {
    const { dispatch } = this.props;
    dispatch(actions.initialLoad());
  };

  onGroupingChange = listName =>
    event => {
      const options = event.target.options;
      for (let i = 0; i < options.length; i++) {
        if (options[i].selected) {
          this.props.dispatch(actions.changeListGrouping(listName, options[i].value));
        }
      }
    };

  setMine = isMine => {
    this.props.dispatch(actions.setMine(isMine));
  };

  render = () =>
    (<Nav
      {...this.props}
      grouping={this.props.grouping}
      onGroupingChange={this.onGroupingChange}
      setMine={this.setMine}
    />);

}
