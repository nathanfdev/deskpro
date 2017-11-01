import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { PaginationBoxView } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Pagination/PaginationBoxView';
import { applyParams } from '../../Actions/publishListActions';

import { connect } from 'react-redux';

@connect(state => ({
  pagination: state.Publish.list.get('pagination')
}))

export class PaginationContainer extends Component {

  static propTypes = {
    dispatch:   PropTypes.func.isRequired,
    pagination: PropTypes.object.isRequired
  };

  handlePageClick = page => {
    this.props.dispatch(applyParams({ page }));
  };

  render = () => {
    const { pagination } = this.props;

    return (
      <PaginationBoxView
        breakLabel={<li><span className="pagination-dots">&hellip;</span></li>}
        pageNum={pagination.total_pages}
        currentPage={pagination.current_page}
        clickCallback={this.handlePageClick}
      />
    );
  }
}
