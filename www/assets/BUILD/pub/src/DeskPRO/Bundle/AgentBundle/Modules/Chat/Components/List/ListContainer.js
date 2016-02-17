import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { paginationSelector, viewModeSelector } from '../../Selectors/list';
import { List } from './List';
import { toggleSelectedAction } from '../../../Application/Actions/massActions';
import { isLoadedCollectionSelectorFactory, setCollection, releaseCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

@connect(state => ({
  isLoaded: isLoadedCollectionSelectorFactory('UserChat', 'chats')(state)
            && isLoadedCollectionSelectorFactory('Department', 'chats')(state),
  pagination: paginationSelector(state),
  viewMode: viewModeSelector(state)
}))
export class ListContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  componentDidMount() {
    this.props.dispatch(setCollection('UserChat', 'chats', []));
    this.props.dispatch(setCollection('Department', 'chats', []));
  }

  componentWillUnmount() {
    this.props.dispatch(releaseCollection('UserChat', 'chats'));
    this.props.dispatch(releaseCollection('Department', 'chats'));
  }

  render() {
    const toggleSelected = (id) => () => this.props.dispatch(toggleSelectedAction(id));
    return (
      <List {...this.props} toggleSelected={toggleSelected}/>
    );
  }
}
