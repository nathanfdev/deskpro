import React from 'react';
import BaseItem from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/BaseItem';

export class FilterItem extends React.Component {
  static propTypes = {
    icon: React.PropTypes.string,
    filterType: React.PropTypes.string.isRequired,
    children: React.PropTypes.any,
    resetFilter: React.PropTypes.func.isRequired,
    isActive: React.PropTypes.bool
  };

  render() {
    return (<BaseItem {...this.props} format="filter" subMenuMode="click">
      {this.props.children}
    </BaseItem>);
  }
}