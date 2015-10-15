import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/GlobalWidgets/DropdownMenu';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { filterDataSelector } from '../../../Selectors/list';

@connect(state => ({
  filterOptions: state.Feedback.list.get('filterOptions').toJS(),
  currentFilterMode: filterDataSelector(state)
}))
export class FilterByDropdownContainer extends Component {

  static propTypes = {
    filterOptions: PropTypes.array.isRequired,
    currentFilterMode: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired
  };

  render() {
    const { filterOptions, currentFilterMode, toggleDropdown } = this.props;

    return (
      <Menu toggleDropdown={toggleDropdown}>
        {filterOptions.map((option, index)=>
            <Option
              key={index}
              active={currentFilterMode.field === option.field}
              callback={this.changeFilter.bind(this)}
              toggleDropdown={toggleDropdown}
              option={option}/>
        )}
        <DropdownMenuFooter>
          <span>Some footer</span>
        </DropdownMenuFooter>
      </Menu>
    );
  }

  changeFilter(event) {
    console.log('We must implement some functionality');
  }
}