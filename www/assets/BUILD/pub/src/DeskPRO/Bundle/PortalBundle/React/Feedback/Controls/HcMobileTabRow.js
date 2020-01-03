import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import map from 'lodash/map';

export class HcMobileTabRow extends React.PureComponent {
  static propTypes = {
    available: PropTypes.object,
    filter:    PropTypes.object,
    setView:   PropTypes.func,
  };

  static iconMap = {
    list:            'fa-list-ul',
    'status-change': 'fa-exchange',
  };

  render() {
    return (
      <div className="dp-po-community-header-mobile-left">
        {map(this.props.available.views, (view, viewId) =>
          <a
            key={viewId}
            className={classNames({ active: this.props.filter.getView() === viewId })}
            href="#setView"
            onClick={(ev) => { ev.preventDefault(); ev.stopPropagation(); this.props.setView(viewId); }}
          >
            <i className={`dp-po-icon far ${HcMobileTabRow.iconMap[viewId]}`} />
          </a>
          )}
      </div>
    );
  }
}
