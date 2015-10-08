import React from "react"
import _ from "lodash"

export default class SortWidget extends React.Component {
  changeSort(e) {
    this.props.setSort(e.target.value)
  }

  render() {
    let sorts = {
      'date-desc': 'Date &darr;',
      'date-asc': 'Date &uarr;',
      'most-views-desc': 'Views &darr;',
      'most-views-asc': 'Views &uarr;',
      'highest-rating-desc': 'Rating &darr;',
      'highest-rating-asc': 'Rating &uarr;',
      'most-popular-desc': 'Popularity &darr;',
      'most-popular-asc': 'Popularity &uarr;',
      'most-discussed-desc': 'Comments &darr;',
      'most-discussed-asc': 'Comments &uarr;'
    };

    let selected_sort = this.props.filter.sort + '-' + this.props.filter.sort_direction;

    return (
      <select style={{float:"right"}} value={selected_sort} onChange={this.changeSort.bind(this)}>
        {_.map(sorts, (title, key) => {
          return (<option key={key} value={key} dangerouslySetInnerHTML={{__html:title}} />);
        })}
      </select>
    );
  }
}
