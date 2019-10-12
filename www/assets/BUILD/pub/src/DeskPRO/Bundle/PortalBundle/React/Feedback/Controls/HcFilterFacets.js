import PropTypes from 'prop-types';
import React from 'react';
import map from 'lodash/map';
import upperFirst from 'lodash/upperFirst';
import includes from 'lodash/includes';

export class HcFilterFacets extends React.Component {
  static propTypes = {
    filter:              PropTypes.object,
    onSetStatus:         PropTypes.func,
    onSetStatusCategory: PropTypes.func,
    onSetActivity:       PropTypes.func,
    onResetActivities:   PropTypes.func,
  };

  static activityIconMap = {
    voted:     'fa-thumbs-up',
    created:   'fa-comment-alt-edit',
    commented: 'fa-comment-alt-lines',
  };

  resetFilters = () => {
    this.props.onResetActivities();
    this.props.onSetStatus('active');
  };

  render() {
    const {
      activities,
      status_categories,
    } = this.props.filter.available;

    return (
      <div className="dp-po-community-header-filter">
        <a
          className="dp-po-community-header-filter-link"
          data-toggle="dropdown"
          aria-haspopup="true"
          aria-expanded="false"
        >
          <i className="dp-po-icon fad fa-align-center" />
          Filters ({this.props.filter.status_categories.length + this.props.filter.activities.length})
          <i className="dp-po-icon far fa-angle-down" />
        </a>
        <div className="dropdown-menu dropdown-menu-left">
          <div className="dp-po-community-header-filter-dropdown">
            <h4 className="dp-po-community-header-filter-dropdown-title">My Activity:</h4>
            {map(activities, (activity, activityId) =>
              <div key={activityId} className="form-group dp-po-form-check">
                <input
                  type="checkbox"
                  className="form-check-input"
                  id={`activity-${activityId}`}
                  checked={includes(this.props.filter.activities, activityId)}
                  onChange={() => this.props.onSetActivity(activityId)}
                />
                <label className="form-check-label" htmlFor={`activity-${activityId}`}>{activity}</label>
                <i className={`dp-po-icon fad ${HcFilterFacets.activityIconMap[activityId]}`} />
              </div>)}
            <hr />
            <h4 className="dp-po-community-header-filter-dropdown-title">Status:</h4>
            <ul className="dp-po-community-header-filter-dropdown-list">
              {map(status_categories, (categories, status) =>
                <li key={status} className="dp-po-community-header-filter-dropdown-item">
                  <div className="form-group dp-po-form-check">
                    <input
                      type="checkbox"
                      className="form-check-input"
                      id={`status_${status}`}
                      checked={status === this.props.filter.status}
                      onChange={() => this.props.onSetStatus(status)}
                    />
                    <label className="form-check-label" htmlFor={`status_${status}`}>{upperFirst(status)}</label>
                  </div>
                  <ul className="dp-po-community-header-filter-dropdown-sublist">
                    {map(categories, (category, categoryId) =>
                      <li key={categoryId} className="dp-po-community-header-filter-dropdown-item">
                        <div className="form-group dp-po-form-check">
                          <input
                            type="checkbox"
                            className="form-check-input"
                            id={`status_category_${category.id}`}
                            checked={includes(this.props.filter.status_categories, category.id)}
                            onChange={() => this.props.onSetStatusCategory(category.id)}
                          />
                          <label className="form-check-label" htmlFor={`status_category_${category.id}`}>
                            <span
                              className="badge badge-secondary"
                              style={{ backgroundColor: category.color }}
                            >
                              {category.title}
                            </span>
                          </label>
                        </div>
                      </li>)}
                  </ul>
                </li>)}
            </ul>
            <hr />
            <a onClick={this.resetFilters} className="dp-po-community-header-filter-dropdown-clear">
              <i className="dp-po-icon fal fa-times" /> Reset all filters
            </a>
          </div>
        </div>
      </div>
    );
  }
}
