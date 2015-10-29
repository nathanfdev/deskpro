import React, {Component, PropTypes} from 'react';
import BaseItem from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/BaseItem';

export class FilterItem extends Component {
  static propTypes = {
    icon: PropTypes.string,
    filterType: PropTypes.string.isRequired,
    children: PropTypes.any,
    resetFilter: PropTypes.func.isRequired,
    isActive: PropTypes.bool
  };

  render() {
    return (<BaseItem {...this.props} format="filter" subMenuMode="click">
      {this.props.children}
    </BaseItem>);
  }
}