import React, { Component, PropTypes } from 'react';
import { Button } from '../Button';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';
import { connect } from 'react-redux';

export class SortingMenu extends Component {
  static propTypes = {
    options: PropTypes.object.isRequired,
    sort: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    sortAction: PropTypes.func.isRequired,
    orderAction: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {expanded: false};
  }

  toggleExpanded = () => this.setState({expanded: !this.state.expanded});
  collapse = () => this.setState({expanded: false});

  render() {
    const { sort, order, options } = this.props;
    const current = options.find(option => option.field === sort);

    return (
      <li>
        <ClickOut onClickOut={this.collapse} onClick={this.toggleExpanded}>
          <Button
            ref="button"
            title="Order by:"
            icon={current ? current.icon : null}
            label={current ? `${current.label} (${order})` : '(no order)'}
          />
          <Positioned isOpen={this.state.expanded}
                      positionAt="left bottom"
                      positionTarget={this.refs.button}>
            <OrderByDropdownContainer {...this.props} />
          </Positioned>
        </ClickOut>
      </li>
    );
  }
}

@connect()
class OrderByDropdownContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    options: PropTypes.object.isRequired,
    sort: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    sortAction: PropTypes.func.isRequired,
    orderAction: PropTypes.func.isRequired
  };

  renderOptions() {
    const { dispatch, sort, sortAction, options } = this.props;
    return (
      options.map((option, index)=>
          <Item
            key={index}
            label={option.label}
            isActive={sort === option.field}
            checked={sort === option.field}
            onClick={() => dispatch(sortAction(option.field))}
            icon={option.icon}
          />
      )
    );
  }

  render() {
    const { dispatch, order, orderAction } = this.props;
    const options = [
      {id: 'asc', onClick: () => dispatch(orderAction('asc')), label: 'Asc'},
      {id: 'desc', onClick: () => dispatch(orderAction('desc')), label: 'Desc'}
    ];

    return (
      <Menu>
        {this.renderOptions()}
        <MenuFooter>
          <MenuFooterOptions options={options} active={order}>
            Sort
          </MenuFooterOptions>
        </MenuFooter>
      </Menu>
    );
  }
}