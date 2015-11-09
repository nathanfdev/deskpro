import React, { PropTypes } from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';
import jQuery from 'jquery';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class OrderByDropdown extends React.Component {

  static propTypes = {
    currentSortOption: PropTypes.object,
    sortOptions: PropTypes.object,
    onToggleListSort: PropTypes.func.isRequired,
    onToggleListOrder: PropTypes.func.isRequired
  };

  render() {
    const { sortOptions = [], onToggleListSort, onToggleListOrder } = this.props;

    return (
      <Menu>
        {jQuery.map(sortOptions, (option, type) =>
          <Item key={type}
                label={option.label}
                isActive={this.props.currentSortOption === type}
                checked={this.props.currentSortOption === type}
                onClick={onToggleListSort.bind(this, type)}
                icon={option.icon} />
        )}

        <MenuFooter>
          <MenuFooterOptions options={[
            { id: constants.ORDER_ASC, onClick: onToggleListOrder.bind(this, 'asc'), label: 'Asc' },
            { id: constants.ORDER_DESC, onClick: onToggleListOrder.bind(this, 'desc'), label: 'Desc' }
          ]}>

            Sort
          </MenuFooterOptions>
        </MenuFooter>
      </Menu>
    );
  }
}
