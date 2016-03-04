import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { Button } from '../Button';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { Item } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import { MenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import { MenuFooterOptions } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';
import jQuery from 'jquery';

export class SortingMenu extends Component {

  static propTypes = {
    options: PropTypes.object.isRequired,
    orderBy: PropTypes.string.isRequired,
    orderDir: PropTypes.string.isRequired,
    orderByAction: PropTypes.func.isRequired,
    orderDirAction: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = { expanded: false };
  }

  toggleExpanded = () => this.setState({ expanded: !this.state.expanded });
  collapse = () => this.setState({ expanded: false });

  render() {
    const { orderBy, orderDir, options } = this.props;
    const current = options[orderBy];

    return (
      <li ref="menuItem">
        <Button isActive={this.state.expanded}
                onClick={this.toggleExpanded}
                ref="button"
                title="Order by:"
                icon={current ? current.icon : null}
                label={current ? `${current.label} (${orderDir})` : '(no order)'}/>

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
    orderBy: PropTypes.string.isRequired,
    orderDir: PropTypes.string.isRequired,
    onMenuUnmount: PropTypes.func,
    orderByAction: PropTypes.func.isRequired,
    orderDirAction: PropTypes.func.isRequired
  };

  componentWillUnmount() {
    const {dispatch, onMenuUnmount} = this.props;

    if (onMenuUnmount) {
      dispatch(onMenuUnmount());
    }
  }

  changeOrder(orderDir, e) {
    e.preventDefault();
    const { dispatch, orderDirAction } = this.props;
    dispatch(orderDirAction(orderDir));
  }

  renderOptions() {
    const { dispatch, orderBy, orderByAction, options } = this.props;

    return (
      jQuery.map(options, (option, type) =>
          <Item key={type}
                label={option.label}
                isActive={orderBy === type}
                checked={orderBy === type}
                onClick={() => dispatch(orderByAction(type))}
                icon={option.icon}/>
      )
    );
  }

  render() {
    const { orderDir } = this.props;
    const options = [
      { id: 'asc', onClick: this.changeOrder.bind(this, 'asc'), label: 'Asc' },
      { id: 'desc', onClick: this.changeOrder.bind(this, 'desc'), label: 'Desc' }
    ];

    return (
      <Menu>
        {this.renderOptions()}
        <MenuFooter>
          <MenuFooterOptions options={options} active={orderDir}>
            Sort
          </MenuFooterOptions>
        </MenuFooter>
      </Menu>
    );
  }
}