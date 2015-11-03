import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { FilterBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { FilterByDropdown } from './FilterByDropdown';
import { isCommentsSelector } from '../../../Selectors/list';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';

@connect(state => ({
  filterParams: state.Feedback.list.get('currentListParams').get('filters'),
  isComments: isCommentsSelector(state),
  statuses: state.Feedback.nav.get('statuses'),
  types: state.Feedback.nav.get('types')
}))

export class FilterContainer extends Component {

  static propTypes = {
    toggleDropdown: PropTypes.func.isRequired,
    expanded: PropTypes.bool.isRequired,
    dispatch: PropTypes.func.isRequired,
    statuses: PropTypes.object.isRequired,
    types: PropTypes.object.isRequired,
    filterParams: PropTypes.object.isRequired
  };

  render() {
    const {dispatch, expanded, toggleDropdown, filterParams, statuses, types, isComments} = this.props;
    let title = 'Filter By';
    let label = '';
    let filtersCounter = 0;
    if (filterParams) {
      title = 'Filtered By:';
      if (filterParams.get('date_created')) {
        label = 'Created';
        filtersCounter++;
      }
      if ((filterParams.get('status') && filterParams.get('status').size > 0)
        ||
        (filterParams.get('status_category') && filterParams.get('status_category').size > 0)) {
        label = 'Status';
        filtersCounter++;
      }
      if (filterParams.get('category') && filterParams.get('category').size > 0) {
        label = 'Type';
        filtersCounter++;
      }
      if (filterParams.get('custom_category') && filterParams.get('custom_category').size > 0) {
        label = 'Type';
        filtersCounter++;
      }
      if (filtersCounter > 1) {
        label = filtersCounter + ' Options';
      }
    }
    return (
      <FilterBy
        title={title}
        label={label}
        toggleDropdown={toggleDropdown}
        ref="filterButton"
        >
        <Positioned isOpen={expanded}
                    positionAt="left bottom"
                    positionTarget={this.refs.filterButton}>
          <FilterByDropdown
            isComments={isComments}
            types={types}
            statuses={statuses}
            filterParams={filterParams}
            toggleDropdown={toggleDropdown}
            dispatch={dispatch}
            />
        </Positioned>
      </FilterBy>
    );
  }
}