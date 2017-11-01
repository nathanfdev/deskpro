import PropTypes from 'prop-types';
// @flow
import React from 'react';
import { Map, List as ImmutableList } from 'immutable';

import { List } from './List';
import { selectedSelector } from '../../../Application/Selectors/massActions';
import { currentAppSelector } from '../../../Application/Selectors/dpWindow';
import {
  isCommentsSelector,
  currentListParamsSelector,
  currentViewModeSelector,
  paginationSelector,
  isLoadedSelector,
  fieldsSelector
} from '../../Selectors/list';
import { toggleSelectedAction } from '../../../Application/Actions/massActions';
import { applyParams } from '../../Actions/FeedbackListActions';
import { connect } from 'react-redux';

type DefaultProps={};
type Props={
  currentApp:string,
  currentViewMode:string,
  applyParams: ()=>void,
  toggleSelectedAction: ()=>void,
  selected: ImmutableList,
  currentListParams: Map,
  fields: Map,
  isComments: boolean,
  isLoaded: boolean
};
type State={};

@connect(
  state => ({
    isComments:        isCommentsSelector(state),
    currentListParams: currentListParamsSelector(state),
    selected:          selectedSelector(state),
    pagination:        paginationSelector(state),
    isLoaded:          isLoadedSelector(state),
    currentApp:        currentAppSelector(state),
    currentViewMode:   currentViewModeSelector(state),
    fields:            fieldsSelector(state)
  }),
  { applyParams, toggleSelectedAction }
)
export class ListContainer extends React.Component<DefaultProps, Props, State> {
  static defaultProps:{};

  static propTypes = {
    currentApp:           PropTypes.string.isRequired,
    isComments:           PropTypes.bool,
    isLoaded:             PropTypes.bool.isRequired,
    currentListParams:    PropTypes.object.isRequired,
    currentViewMode:      PropTypes.string.isRequired,
    fields:               PropTypes.object.isRequired,
    toggleSelectedAction: PropTypes.func.isRequired,
    applyParams:          PropTypes.func.isRequired
  };

  state:State;

  render() {
    const toggleSelected  = (id:number) => () => this.props.toggleSelectedAction(id);
    const handlePageClick = (page:number) => this.props.applyParams({ page });

    return (
      <List {...this.props} toggleSelected={toggleSelected} handlePageClick={handlePageClick} />
    );
  }
}
