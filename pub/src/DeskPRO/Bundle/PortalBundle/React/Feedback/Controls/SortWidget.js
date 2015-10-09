import React from "react"
import _ from "lodash"

export default class SortWidget extends React.Component {
  changeSort(e) {
    this.props.setSort(e.target.value)
  }

  render() {
    let sorts = {
      'date-desc': 'Date ' + String.fromCharCode(8595),
      'date-asc': 'Date ' + String.fromCharCode(8593),
      'most-views-desc': 'Views ' + String.fromCharCode(8595),
      'most-views-asc': 'Views ' + String.fromCharCode(8593),
      'highest-rating-desc': 'Rating ' + String.fromCharCode(8595),
      'highest-rating-asc': 'Rating ' + String.fromCharCode(8593),
      'most-popular-desc': 'Popularity ' + String.fromCharCode(8595),
      'most-popular-asc': 'Popularity ' + String.fromCharCode(8593),
      'most-discussed-desc': 'Comments ' + String.fromCharCode(8595),
      'most-discussed-asc': 'Comments ' + String.fromCharCode(8593)
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
