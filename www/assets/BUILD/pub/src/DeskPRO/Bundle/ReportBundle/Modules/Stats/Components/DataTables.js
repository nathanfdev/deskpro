import $ from 'jquery';

import PropTypes from 'prop-types';
import React from 'react';

class DataTable extends React.Component {

  static propTypes = {
    columns: PropTypes.array.isRequired,
    data:    PropTypes.array.isRequired,
  };


  componentDidMount() {
    $(this.el).DataTable({
      dom:            '<"data-table-wrapper"t>',
      data:           this.props.data,
      columns:        this.props.columns,
      pagingType:     'first_last_numbers',
      searching:      false,
      pageLength:     5,
      bJQueryUI:      true,
      iDisplayLength: 5,
      sDom:           'T<"clear">lfrtip',
      lengthMenu:     [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'All']],
      deferRender:    true,
      scrollCollapse: true,
      autoWidth:      true
    });
  }

  // eslint-disable-next-line class-methods-use-this
  shouldComponentUpdate() {
    return false; // sic(!)
  }

  // eslint-disable-next-line class-methods-use-this
  componentWillUnmount() {
    $(this.el)
      .DataTable()
      .destroy(true);
  }

  render() {
    return (
      <div>
        <table ref={(el) => { this.el = el; }} />
      </div>);
  }
}

export default DataTable;
