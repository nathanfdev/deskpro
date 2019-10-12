import PropTypes from 'prop-types';
import React from 'react';
import map from 'lodash/map';

export class HcTabRow extends React.Component {
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
      <div className="dp-po-community-tabs">
        <ul className="dp-po-community-tabs-list">
          {map(this.props.available.views, (view, viewId) =>
            <li key={viewId} className={`dp-po-community-tabs-item ${this.props.filter.getView() === viewId && 'active'}`}>
              <a className="dp-po-community-tabs-link" onClick={() => this.props.setView(viewId)}>
                <i className={`dp-po-icon far ${HcTabRow.iconMap[viewId]}`} />
                {view}
              </a>
            </li>
          )}
        </ul>
      </div>
    );
  }
}
