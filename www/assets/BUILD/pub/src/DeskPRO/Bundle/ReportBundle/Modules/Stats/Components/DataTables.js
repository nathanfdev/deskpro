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
    options: {}
  };

  componentDidMount() {
    const { data, columns, options } = this.props;

    $(this.el).DataTable({
      data,
      columns,
      dom:            '<"data-table-wrapper"t>',
      pagingType:     'first_last_numbers',
      searching:      false,
      bJQueryUI:      true,
      iDisplayLength: 5,
      sDom:           'T<"clear">lfrtip',
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
      <div>
        <table ref={(el) => { this.el = el; }} />
      </div>);
  }
}

export default DataTable;
