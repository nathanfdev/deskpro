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
    this.originalData = data;
    const $table = $(this.el);

    this.dt = $table.DataTable({
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

  resetClick = () => {
    if (this.dt) {
      this.dt.order([]).clear().rows.add(this.originalData).draw();
    }
  };

  render() {
    return (
      <div style={{ height: this.props.options.height ? this.props.options.height : 200 }}>
        <table ref={(el) => { this.el = el; }}>
          <tfoot>
            <tr className="dataTables_reset_wrapper">
              <td colSpan={this.props.columns.length}><span className="dataTables_reset" onClick={this.resetClick}>Reset order | </span></td>
            </tr>
          </tfoot>
        </table>
      </div>);
  }
}

export default DataTable;
