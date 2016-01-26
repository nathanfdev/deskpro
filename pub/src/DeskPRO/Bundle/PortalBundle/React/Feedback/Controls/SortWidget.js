import React from "react"
import _ from "lodash"
import PortalPhrases from "DeskPRO/Bundle/PortalBundle/PortalPhrases"

export default class SortWidget extends React.Component {
  changeSort(e) {
    this.props.setSort(e.target.value)
  }

  render() {
    let sorts = {
      'date-desc':           PortalPhrases.get('portal.general.prop_date') + String.fromCharCode(8595),
      'date-asc':            PortalPhrases.get('portal.general.prop_date') + String.fromCharCode(8593),
      'most-views-desc':     PortalPhrases.get('portal.general.prop_views') + String.fromCharCode(8595),
      'most-views-asc':      PortalPhrases.get('portal.general.prop_views') + String.fromCharCode(8593),
      'highest-rating-desc': PortalPhrases.get('portal.general.prop_rating') + String.fromCharCode(8595),
      'highest-rating-asc':  PortalPhrases.get('portal.general.prop_rating') + String.fromCharCode(8593),
      'most-popular-desc':   PortalPhrases.get('portal.general.prop_popularity')+ String.fromCharCode(8595),
      'most-popular-asc':    PortalPhrases.get('portal.general.prop_popularity') + String.fromCharCode(8593),
      'most-discussed-desc': PortalPhrases.get('portal.general.prop_comments') + String.fromCharCode(8595),
      'most-discussed-asc':  PortalPhrases.get('portal.general.prop_comments') + String.fromCharCode(8593)
    };

    let selected_sort = this.props.filter.sort + '-' + this.props.filter.sort_direction;

    return (
      <select style={{float:"right"}} value={selected_sort} onChange={this.changeSort.bind(this)}>
        {_.map(sorts, (title, key) => {
          return (<option key={key} value={key}>{title}</option>);
        })}
      </select>
    );
  }
}
