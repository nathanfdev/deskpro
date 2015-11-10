import React, { PropTypes } from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';


export class OrderByDropdown extends React.Component {

  static propTypes = {
    currentSortOption: PropTypes.object,
    sortOptions: PropTypes.object
  };

  toggleListOrder = value => {
    console.log(value);
  };

  renderOption(option, index) {
    return (
      <Item key={index}
            label={option.label}
            isActive={this.props.currentSortOption === option}
            icon={option.icon} />
    );
  }

  render() {
    const { sortOptions = [] } = this.props;

    return (
      <Menu>
        {sortOptions.map((option, index) => this.renderOption(option, index))}

        <MenuFooter>
          <MenuFooterOptions options={[
            {id: 'asc', onClick: this.toggleListOrder.bind(this, 'asc'), label: 'Asc'},
            {id: 'desc', onClick: this.toggleListOrder.bind(this, 'desc'), label: 'Desc'}
          ]}>

            Sort
          </MenuFooterOptions>
        </MenuFooter>
      </Menu>
    );
  }
}
