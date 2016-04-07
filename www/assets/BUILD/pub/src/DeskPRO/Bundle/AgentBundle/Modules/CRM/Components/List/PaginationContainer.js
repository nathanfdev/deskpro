import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { PaginationBoxView } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Pagination/PaginationBoxView';
import { applyParams } from '../../Actions/crmListActions';

@connect(state => ({
  pagination: state.CRM.list.get('pagination')
}))

export class PaginationContainer extends Component {

  static propTypes = {
    dispatch:   PropTypes.func.isRequired,
    pagination: PropTypes.object.isRequired
  };

  handlePageClick = (page) => {
    this.props.dispatch(applyParams({ page }));
  };

  render() {
    const { pagination } = this.props;
    return (
      <PaginationBoxView breakLabel={<li><span className="pagination-dots">&hellip;</span></li>}
                         pageNum={pagination.total_pages}
                         currentPage={pagination.current_page}
                         clickCallback={this.handlePageClick} />
    );
  }
}
