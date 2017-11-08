import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Button } from '../Button';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { Item } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import { MenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import { MenuFooterOptions } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';

export class SortingMenu extends Component {

  static propTypes = {
    options:       PropTypes.object.isRequired,
    currentParams: PropTypes.object.isRequired,
    setParam:      PropTypes.func.isRequired,
    onMenuUnmount: PropTypes.func.isRequired
  };

  componentWillMount() {
    this.setState({
      expanded: false
    });
  }

  toggleExpanded = () => this.setState({ expanded: !this.state.expanded });
  collapse = () => this.setState({ expanded: false });

  render() {
    const { options, currentParams } = this.props;
    const current = options[currentParams.order_by];

    return (
      <li ref="menuItem">
        <Button isActive={this.state.expanded}
          onClick={this.toggleExpanded}
          ref="button"
          title="Order by:"
          icon={current ? current.icon : null}
          label={current ? `${current.label} (${currentParams.order_dir})` : '(no order)'}
        />

        <Detached isOpen={this.state.expanded} positionAt="left bottom" positionTarget={this.refs.button}>
          <ClickOut onClickOut={this.collapse} ignoreNodes={[this.refs.menuItem]}>
            <OrderByDropdownContainer {...this.props} />
          </ClickOut>
        </Detached>
      </li>
    );
  }
}

class OrderByDropdownContainer extends Component {

  static propTypes = {
    setParam:      PropTypes.func.isRequired,
    currentParams: PropTypes.object.isRequired,
    options:       PropTypes.object.isRequired,
    onMenuUnmount: PropTypes.func.isRequired
  };

  componentWillUnmount() {
    this.props.onMenuUnmount();
  }

  setOrder = (orderBy) => {
    this.props.setParam({ param: 'order_by', value: orderBy });
    if (!this.orderDir) {
      this.changeOrder('asc');
    }
  };

  changeOrder = (orderDir, e) => {
    if (e) {
      e.preventDefault();
    }
    this.orderDir = orderDir;
    const { setParam } = this.props;
    setParam({
      param: 'order_dir',
      value: orderDir
    });
  };

  renderOptions() {
    const { options, currentParams } = this.props;

    return (
      Object.entries(options).map(([type, option]) =>
        <Item key={type}
          label={option.label}
          isActive={currentParams.order_by === type}
          checked={currentParams.order_by === type}
          onClick={() => this.setOrder(type)}
          icon={option.icon}
        />
      )
    );
  }

  render() {
    const { currentParams } = this.props;
    const options = [
      {
        id:      'asc',
        onClick: e => this.changeOrder('asc', e),
        label:   'Asc'
      },
      {
        id:      'desc',
        onClick: e => this.changeOrder('desc', e),
        label:   'Desc'
      }
    ];

    return (
      <Menu>
        {this.renderOptions()}
        <MenuFooter>
          <MenuFooterOptions options={options} active={currentParams.order_dir}>
            Sort
          </MenuFooterOptions>
        </MenuFooter>
      </Menu>
    );
  }
}
