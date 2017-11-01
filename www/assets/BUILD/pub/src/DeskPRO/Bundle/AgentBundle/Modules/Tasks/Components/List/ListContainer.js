import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { pureRender } from 'DeskPRO/Component/Ampliflux';
import { List } from './List';
import { selectedSelector } from '../../../Application/Selectors/massActions';
import { currentViewModeSelector, listParamsNavSelector, isLoadedSelector, paginationSelector } from '../../Selectors/list';
import { applyFilters } from '../../Actions/listActions';

@connect(
  state => ({
    isLoaded:    isLoadedSelector(state),
    currentView: currentViewModeSelector(state),
    selected:    selectedSelector(state),
    currentNav:  listParamsNavSelector(state),
    pagination:  paginationSelector(state)
  }),
  { applyFilters }
)

@pureRender

export class ListContainer extends React.Component {
  static propTypes = {
    applyFilters: PropTypes.func.isRequired
  };

  render() {
    const handlePageClick = page => this.props.applyFilters({ page });

    return <List {...this.props} handlePageClick={handlePageClick} />;
  }
}
