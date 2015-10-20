import React, {Component, PropTypes} from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
export class FilterByDropdown extends Component {

  static propTypes = {
    filterOptions: PropTypes.array.isRequired,
    currentFilterMode: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired
  };

  changeFilter() {
    console.log('We must implement some functionality');
  }

  render() {
    const { filterOptions, currentFilterMode, toggleDropdown } = this.props;

    return (
      <Menu>
        {filterOptions.map((option, index)=>
            <Item
              key={index}
              isActive={currentFilterMode.field === option.field}
              callback={this.changeFilter.bind(this)}
              toggleDropdown={toggleDropdown}
              icon={option.icon}
              label={option.label}/>
        )}
      </Menu>
    );
  }
}