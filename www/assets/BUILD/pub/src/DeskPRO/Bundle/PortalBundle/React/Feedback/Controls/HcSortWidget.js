import PropTypes from 'prop-types';
import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import map from 'lodash/map';

export class HcSortWidget extends React.Component {

  static propTypes = {
    setSort: PropTypes.func,
  };

  render() {
    const sorts = {
      'most-popular-desc':   portalPhrases.get('portal.general.prop_popularity_desc'),
      'most-popular-asc':    portalPhrases.get('portal.general.prop_popularity_asc'),
      'highest-rating-desc': portalPhrases.get('portal.general.prop_rating_desc'),
      'highest-rating-asc':  portalPhrases.get('portal.general.prop_rating_asc'),
      'date-desc':           portalPhrases.get('portal.general.prop_date_desc'),
      'date-asc':            portalPhrases.get('portal.general.prop_date_asc'),
      'most-discussed-desc': portalPhrases.get('portal.general.prop_comments_desc'),
      'most-discussed-asc':  portalPhrases.get('portal.general.prop_comments_asc'),
      'most-views-desc':     portalPhrases.get('portal.general.prop_views_desc'),
      'most-views-asc':      portalPhrases.get('portal.general.prop_views_asc')
    };

    return (
      <div className="float-right" style={{ minWidth: '120px' }}>
        <div className="dp-po-community-header-sort">
          <a className="dp-po-community-header-sort-link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i className="dp-po-icon fad fa-sort-alt" />
          </a>
          <div className="dropdown-menu dropdown-menu-right">
            {map(sorts, (sort, id) =>
              <a key={id} onClick={() => this.props.setSort(id)} className="dropdown-item">{sort} <i className="dp-po-icon far fa-check" /></a>
            )}
          </div>
        </div>
      </div>
    );
  }
}
