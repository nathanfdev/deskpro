import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { FilterBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { filterDataSelector } from '../../../Selectors/list';
import { FilterByDropdown } from './FilterByDropdown';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';

@connect(state => ({
  filterOptions: state.Feedback.list.get('filterOptions').toJS(),
  currentFilterMode: filterDataSelector(state)
}))

export class FilterContainer extends Component {

  static propTypes = {
    toggleDropdown: PropTypes.func.isRequired,
    expanded: PropTypes.bool.isRequired,
    currentFilterMode: PropTypes.object.isRequired,
    filterOptions: PropTypes.array.isRequired
  };

  render() {
    const {expanded, currentFilterMode, toggleDropdown, filterOptions} = this.props;
    return (
      <FilterBy
        currentFilterMode={currentFilterMode}
        toggleDropdown={toggleDropdown}
        ref="filterButton"
        >
        <Positioned isOpen={expanded}
                    positionAt="left bottom"
                    positionTarget={this.refs.filterButton}>
          <FilterByDropdown
            filterOptions={filterOptions}
            toggleDropdown={toggleDropdown}
            currentFilterMode={currentFilterMode}
            />
        </Positioned>
      </FilterBy>
    );
  }
}