import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import map from 'lodash/map';
import upperFirst from 'lodash/upperFirst';
import includes from 'lodash/includes';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { HcFilterFacets } from './HcFilterFacets';

export class HcMobileFilters extends React.Component {
  static propTypes = {
    filter:              PropTypes.object,
    onSetStatus:         PropTypes.func,
    onSetStatusCategory: PropTypes.func,
    onSetActivity:       PropTypes.func,
    onResetActivities:   PropTypes.func,
    setSort:             PropTypes.func,
  };

  constructor(props) {
    super(props);

    this.state = {
      currentModal: null
    };
  }

  onSort = (id) => {
    this.props.setSort(id);
    this.setModal(null);
  };

  setModal = (currentModal) => {
    this.setState({
      currentModal
    });
  };

  resetFilters = (ev) => {
    ev.preventDefault();
    ev.stopPropagation();
    this.props.onResetActivities();
    this.props.onSetStatus('active');
    this.setModal(null);
  };

  renderSortModal() {
    const sorts = {
      'most-popular-desc':   portalPhrases.get('helpcenter.general.prop_popularity_desc'),
      'most-popular-asc':    portalPhrases.get('helpcenter.general.prop_popularity_asc'),
      'highest-rating-desc': portalPhrases.get('helpcenter.general.prop_rating_desc'),
      'highest-rating-asc':  portalPhrases.get('helpcenter.general.prop_rating_asc'),
      'date-desc':           portalPhrases.get('helpcenter.general.prop_date_desc'),
      'date-asc':            portalPhrases.get('helpcenter.general.prop_date_asc'),
      'most-discussed-desc': portalPhrases.get('helpcenter.general.prop_comments_desc'),
      'most-discussed-asc':  portalPhrases.get('helpcenter.general.prop_comments_asc'),
      'most-views-desc':     portalPhrases.get('helpcenter.general.prop_views_desc'),
      'most-views-asc':      portalPhrases.get('helpcenter.general.prop_views_asc')
    };

    return (
      <div className="modal-dialog" role="document">
        <div className="modal-content">
          <div className="modal-header">
            <button type="button" className="close" aria-label="Close" onClick={() => this.setModal(null)}>
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div className="modal-body">
            {map(sorts, (sort, id) =>
              <a href="#sort" key={id} onClick={(ev) => { ev.preventDefault(); ev.stopPropagation(); this.onSort(id); }} className="dropdown-item">{sort}</a>
            )}
          </div>
        </div>
      </div>
    );
  }

  renderActivitiesSelection = () => {
    const { activities } = this.props.filter.available;

    const phrases = {
      myActivity: portalPhrases.get('helpcenter.community.my-activity'),
    };

    return (
      <div>
        <h4 className="dp-po-community-header-filter-dropdown-title">{phrases.myActivity}:</h4>
        {map(activities, (activity, activityId) =>
          <div key={activityId} className="form-group dp-po-form-check">
            <input
              type="checkbox"
              className="form-check-input cursor-pointer"
              id={`activity-${activityId}`}
              checked={includes(this.props.filter.activities, activityId)}
              onChange={() => this.props.onSetActivity(activityId)}
            />
            <label className="form-check-label" htmlFor={`activity-${activityId}`}>{activity}</label>
            <i className={`dp-po-icon fad ${HcFilterFacets.activityIconMap[activityId]}`} />
          </div>)}
        <hr />
      </div>
    );
  };

  renderFilterModal() {
    const { status_categories } = this.props.filter.available;

    const phrases = {
      resetAllFilters: portalPhrases.get('helpcenter.community.reset-all-filters'),
      status:          portalPhrases.get('helpcenter.community.status'),
    };

    return (
      <div className="modal-dialog" role="document">
        <div className="modal-content">
          <div className="modal-header">
            <button type="button" className="close" aria-label="Close" onClick={() => this.setModal(null)}>
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div className="modal-body">
            <div className="dp-po-community-header-filter-dropdown">
              {window.DESKPRO_USER_AVAILABLE && this.renderActivitiesSelection()}
              <hr />
              <h4 className="dp-po-community-header-filter-dropdown-title">{phrases.status}:</h4>
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
              <a onClick={this.resetFilters} href="#resetFilters" className="dp-po-community-header-filter-dropdown-clear">
                <i className="dp-po-icon fal fa-times" /> {phrases.resetAllFilters}
              </a>
            </div>
          </div>
        </div>
      </div>
    );
  }

  render() {
    const { currentModal } = this.state;
    return (
      <div className="dp-po-community-header-mobile-right">
        <a onClick={() => this.setModal('sort')}><i
          className="dp-po-icon fad fa-sort-alt"
        /> {portalPhrases.get('helpcenter.general.sort')}</a>
        <a onClick={() => this.setModal('filter')}><i
          className="dp-po-icon fad fa-align-center"
        /> {portalPhrases.get('helpcenter.general.filter')}</a>
        <div
          className={classNames('modal fade mobile-modal', { show: currentModal === 'sort' })} id="communitySort" tabIndex="-1" role="dialog"
          aria-labelledby="communitySort"
          aria-hidden="true"
          style={{ display: currentModal === 'sort' ? 'block' : 'none' }}
        >
          {this.renderSortModal()}
        </div>


        <div
          className={classNames('modal fade mobile-modal', { show: currentModal === 'filter' })} id="communityFilter" tabIndex="-1" role="dialog"
          aria-labelledby="communityFilter"
          aria-hidden="true"
          style={{ display: currentModal === 'filter' ? 'block' : 'none' }}
        >
          {this.renderFilterModal()}
        </div>
        {currentModal !== null && <div className="modal-backdrop fade show" />}
      </div>
    );
  }
}
