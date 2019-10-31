import PropTypes from 'prop-types';
import React from 'react';

export class HcViewModes extends React.Component {
  static propTypes = {
    filter:        PropTypes.object,
    onSetViewMode: PropTypes.func,
  };

  onClickCompact = (ev) => {
    ev.preventDefault();
    ev.stopPropagation();
    this.props.onSetViewMode('compact');
  };

  onClickExpanded = (ev) => {
    ev.preventDefault();
    ev.stopPropagation();
    this.props.onSetViewMode('expanded');
  };

  render() {
    const viewMode = this.props.filter.view_mode;

    return (
      <div className="dp-po-community-header-view">
        <a href="#setCompact" onClick={this.onClickCompact} className={`${viewMode === 'compact' && 'active'} dp-po-community-header-view-link dp-po-community-header-view-compact`}>
          <i className="dp-po-icon far fa-minus" />
        </a>
        <a href="#setExpanded" onClick={this.onClickExpanded} className={`${viewMode === 'expanded' && 'active'} dp-po-community-header-view-link dp-po-community-header-view-expanded`}>
          <i className="dp-po-icon far fa-equals" />
        </a>
      </div>
    );
  }
}
