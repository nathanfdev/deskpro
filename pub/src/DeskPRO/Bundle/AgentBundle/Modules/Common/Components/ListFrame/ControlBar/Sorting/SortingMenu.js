import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { Button } from '../Button';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';
import jQuery from 'jquery';

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
    this.state = { expanded: false };
  }

  toggleExpanded = () => this.setState({ expanded: !this.state.expanded });
  collapse = () => this.setState({ expanded: false });

  render() {
    const { sort, order, options } = this.props;
    const current = options[sort];

    return (
      <li ref="menuItem">
        <Button
          isActive={this.state.expanded}
          onClick={this.toggleExpanded}
          ref="button"
          title="Order by:"
          icon={current ? current.icon : null}
          label={current ? `${current.label} (${order})` : '(no order)'}
          />

        <Detached isOpen={this.state.expanded}
                  positionAt="left bottom"
                  positionTarget={this.refs.button}>

          <ClickOut onClickOut={this.collapse} ignoreNodes={[this.refs.menuItem]}>
            <OrderByDropdownContainer {...this.props} />
          </ClickOut>
        </Detached>
      </li>
    );
  }
}

@connect() class OrderByDropdownContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    options: PropTypes.object.isRequired,
    sort: PropTypes.string.isRequired,
    order: PropTypes.string.isRequired,
    onMenuUnmount: PropTypes.func,
    sortAction: PropTypes.func.isRequired,
    orderAction: PropTypes.func.isRequired
  };

  componentWillUnmount() {
    const {dispatch, onMenuUnmount} = this.props;

    if (onMenuUnmount) {
      dispatch(onMenuUnmount());
    }
  }

  renderOptions() {
    const { dispatch, sort, sortAction, options } = this.props;
    return (
      jQuery.map(options, (option, type) =>
        <Item
          key={type}
          label={option.label}
          isActive={sort === type}
          checked={sort === type}
          onClick={() => dispatch(sortAction(type))}
          icon={option.icon}
        />
      )
    );
  }

  render() {
    const { dispatch, order, orderAction } = this.props;
    const options = [
      { id: 'asc', onClick: () => dispatch(orderAction('asc')), label: 'Asc' },
      { id: 'desc', onClick: () => dispatch(orderAction('desc')), label: 'Desc' }
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