import React, { PropTypes } from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import _ from 'lodash';

export class SortWidget extends React.Component {

  static propTypes = {
    setSort: PropTypes.func,
    filter: PropTypes.object
  };

  onChangeSort = event => {
    this.props.setSort(event.target.value);
  };

  render() {
    const { filter } = this.props;

    const arrowDown = String.fromCharCode(8595);
    const arrowUp = String.fromCharCode(8593);

    const sorts = {
      'date-desc': portalPhrases.get('portal.general.prop_date') + arrowDown,
      'date-asc': portalPhrases.get('portal.general.prop_date') + arrowUp,
      'most-views-desc': portalPhrases.get('portal.general.prop_views') + arrowDown,
      'most-views-asc': portalPhrases.get('portal.general.prop_views') + arrowUp,
      'highest-rating-desc': portalPhrases.get('portal.general.prop_rating') + arrowDown,
      'highest-rating-asc': portalPhrases.get('portal.general.prop_rating') + arrowUp,
      'most-popular-desc': portalPhrases.get('portal.general.prop_popularity') + arrowDown,
      'most-popular-asc': portalPhrases.get('portal.general.prop_popularity') + arrowUp,
      'most-discussed-desc': portalPhrases.get('portal.general.prop_comments') + arrowDown,
      'most-discussed-asc': portalPhrases.get('portal.general.prop_comments') + arrowUp
    };

    const selectedSort = filter.sort + '-' + filter.sort_direction;

    return (
      <select style={{float: 'right'}} value={selectedSort} onChange={this.onChangeSort}>
        {_.map(sorts, (title, key) => <option key={key} value={key}>{title}</option>)}
      </select>
    );
  }
}
