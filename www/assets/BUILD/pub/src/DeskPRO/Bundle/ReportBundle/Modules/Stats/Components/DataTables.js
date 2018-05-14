import $ from 'jquery';

import PropTypes from 'prop-types';
import React from 'react';

class DataTable extends React.Component {

  static propTypes = {
    columns: PropTypes.array.isRequired,
    data:    PropTypes.array.isRequired,
    options: PropTypes.object
  };

  static defaultProps = {
    options: {
      height: 200
    }
  };

  componentDidMount() {
    const { data, columns, options } = this.props;
    const $table = $(this.el);
    const self = this;

    $table.DataTable({
      data,
      columns,
      dom:            '<"data-table-wrapper"t>',
      pagingType:     'first_last_numbers',
      searching:      false,
      bJQueryUI:      true,
      iDisplayLength: 5,
      sDom:           'T<"clear">lfrtip',
      deferRender:    true,
      fnDrawCallback: (settings) => {
        // eslint-disable-next-line no-underscore-dangle
        if (settings._iDisplayLength === -1 || settings._iDisplayLength >= settings.fnRecordsDisplay()) {
          $(settings.nTableWrapper).find('.dataTables_paginate').hide();
        } else {
          $(settings.nTableWrapper).find('.dataTables_paginate').show();
        }
        console.log(self.props.options.height);
      },
      ...options
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
      <div style={{ height: this.props.options.height ? this.props.options.height : 200 }}>
        <table ref={(el) => { this.el = el; }} />
      </div>);
  }
}

export default DataTable;
