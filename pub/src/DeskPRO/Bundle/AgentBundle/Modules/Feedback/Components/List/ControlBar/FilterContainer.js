import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { FilterBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { FilterByDropdown } from './FilterByDropdown';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';

@connect(state => ({
  listParams: state.Feedback.list.get('currentListParams')
}))

export class FilterContainer extends Component {

  static propTypes = {
    toggleDropdown: PropTypes.func.isRequired,
    expanded: PropTypes.bool.isRequired,
    dispatch: PropTypes.func.isRequired,
    listParams: PropTypes.object.isRequired
  };

  render() {
    const {dispatch, expanded, toggleDropdown, listParams} = this.props;
    return (
      <FilterBy
        filterParams={listParams.get('filters')}
        toggleDropdown={toggleDropdown}
        ref="filterButton"
        >
        <Positioned isOpen={expanded}
                    positionAt="left bottom"
                    positionTarget={this.refs.filterButton}>
          <FilterByDropdown
            filterParams={listParams.get('filters')}
            toggleDropdown={toggleDropdown}
            dispatch={dispatch}
            />
        </Positioned>
      </FilterBy>
    );
  }
}