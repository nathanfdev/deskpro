import PropTypes from 'prop-types';
import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import PortalSimpleSelectBox from 'DeskPRO/Bundle/PortalBundle/React/Form/PortalSimpleSelectBox';
import map from 'lodash/map';

export class SortWidget extends React.Component {

  static propTypes = {
    setSort: PropTypes.func,
    filter:  PropTypes.object
  };

  onChangeSort = (option) => {
    this.props.setSort(option.id);
  };

  render() {
    const { filter } = this.props;
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

    const selectedSort = { id: `${filter.sort}-filter.sort_direction`, title: 'Sort' };
    if (sorts[selectedSort.id]) {
      selectedSort.title = sorts[selectedSort.id];
    }

    const options = map(sorts, (title, id) => ({ id, title }));
    const widgetOptions = {
      widgetClassName: ['small', 'borderless', 'right']
    };

    return (
      <div className="float-right" style={{ minWidth: '120px' }}>
        <PortalSimpleSelectBox
          widgetOptions={widgetOptions}
          options={options}
          value={selectedSort}
          onChange={this.onChangeSort}
        />
      </div>
    );
  }
}
