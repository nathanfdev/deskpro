import React, { PropTypes } from 'react';
import _ from 'lodash';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export default class SortWidget extends React.Component {

  static propTypes = {
    setSort: PropTypes.func,
    filter: PropTypes.object
  };

  changeSort(e) {
    this.props.setSort(e.target.value);
  }

  render() {
    const { filter } = this.props;
    const sorts = {
      'date-desc': portalPhrases.get('portal.general.prop_date') + String.fromCharCode(8595),
      'date-asc': portalPhrases.get('portal.general.prop_date') + String.fromCharCode(8593),
      'most-views-desc': portalPhrases.get('portal.general.prop_views') + String.fromCharCode(8595),
      'most-views-asc': portalPhrases.get('portal.general.prop_views') + String.fromCharCode(8593),
      'highest-rating-desc': portalPhrases.get('portal.general.prop_rating') + String.fromCharCode(8595),
      'highest-rating-asc': portalPhrases.get('portal.general.prop_rating') + String.fromCharCode(8593),
      'most-popular-desc': portalPhrases.get('portal.general.prop_popularity') + String.fromCharCode(8595),
      'most-popular-asc': portalPhrases.get('portal.general.prop_popularity') + String.fromCharCode(8593),
      'most-discussed-desc': portalPhrases.get('portal.general.prop_comments') + String.fromCharCode(8595),
      'most-discussed-asc': portalPhrases.get('portal.general.prop_comments') + String.fromCharCode(8593)
    };

    const selectedSort = filter.sort + '-' + filter.sort_direction;

    return (
      <select style={{float: 'right'}} value={selectedSort} onChange={this.changeSort.bind(this)}>
        {_.map(sorts, (title, key) => {
          return (<option key={key} value={key}>{title}</option>);
        })}
      </select>
    );
  }
}
