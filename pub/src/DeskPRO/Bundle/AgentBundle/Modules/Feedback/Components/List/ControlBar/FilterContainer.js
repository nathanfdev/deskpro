import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { FilterBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { filterDataSelector } from '../../../Selectors/list';
import { FilterByDropdownContainer } from './FilterByDropdownContainer';

@connect(state => ({
  currentFilterMode: filterDataSelector(state)
}))

export class FilterContainer extends Component {

  static propTypes = {
    toggleDropdown: PropTypes.func.isRequired
  };

  renderDropdown() {
    const {expanded, offset, toggleDropdown} = this.props;
    if (expanded) {
      return (
        <FilterByDropdownContainer
          offset={offset}
          toggleDropdown={toggleDropdown}
          />
      );
    }
  }

  render() {
    const {currentFilterMode, toggleDropdown} = this.props;
    return (
      <FilterBy
        currentFilterMode={currentFilterMode}
        toggleDropdown={toggleDropdown}
        >
        {this.renderDropdown()}
      </FilterBy>
    );
  }
}