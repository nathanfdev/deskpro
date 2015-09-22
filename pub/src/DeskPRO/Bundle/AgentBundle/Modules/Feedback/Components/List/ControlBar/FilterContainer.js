import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { FilterBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { filterDataSelector } from '../../../Selectors/list';

@connect(state => ({
  currentFilterMode: filterDataSelector(state)
}))

export class FilterContainer extends React.Component {

  static propTypes = {
    toggleDropdown: PropTypes.func.isRequired
  };

  render() {
    const {currentFilterMode, toggleDropdown} = this.props;
    return (
      <FilterBy
        currentFilterMode={currentFilterMode}
        toggleDropdown={toggleDropdown}
        />
    )
  }
}