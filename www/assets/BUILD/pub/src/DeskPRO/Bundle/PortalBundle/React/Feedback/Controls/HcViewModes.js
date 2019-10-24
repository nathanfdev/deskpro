import PropTypes from 'prop-types';
import React from 'react';

export class HcViewModes extends React.Component {
  static propTypes = {
    filter:        PropTypes.object,
    onSetViewMode: PropTypes.func,
  };

  render() {
    const viewMode = this.props.filter.view_mode;

    return (
      <div className="dp-po-community-header-view">
        <a onClick={() => this.props.onSetViewMode('compact')} className={`${viewMode === 'compact' && 'active'} dp-po-community-header-view-link dp-po-community-header-view-compact`}>
          <i className="dp-po-icon far fa-minus" />
        </a>
        <a onClick={() => this.props.onSetViewMode('expanded')} className={`${viewMode === 'expanded' && 'active'} dp-po-community-header-view-link dp-po-community-header-view-expanded`}>
          <i className="dp-po-icon far fa-equals" />
        </a>
      </div>
    );
  }
}
