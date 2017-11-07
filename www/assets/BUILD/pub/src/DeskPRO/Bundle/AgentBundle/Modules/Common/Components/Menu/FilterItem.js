import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { BaseItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/BaseItem';

import { connect } from 'react-redux';
@connect()
export class FilterItem extends Component {
  static propTypes = {
    icon:     PropTypes.string,
    children: PropTypes.any,
    dispatch: PropTypes.func.isRequired,
    isActive: PropTypes.bool
  };

  renderFilterInfo = (labels) => {
    if (labels.length) {
      const result = [<span key={0} className="dpw-navigation-dropdown-item-inline-info">{labels[0]}</span>];
      if (labels.length > 1) {
        result.push(
          <span key={1} className="dpw-navigation-dropdown-item-inline-info dpw-navigation-dropdown-item-inline-info-extra">
            +{labels.length - 1}
          </span>
        );
      }

      return result;
    }

    return <span />;
  };

  render() {
    return (
      <BaseItem {...this.props} format="filter"
        subMenuMode="click"
        hasMenu
        renderFilterInfo={this.renderFilterInfo}
      >
        {this.props.children}
      </BaseItem>
    );
  }
}
