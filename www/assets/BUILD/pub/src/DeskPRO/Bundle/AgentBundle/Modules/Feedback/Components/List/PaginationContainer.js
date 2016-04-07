import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { PaginationBoxView } from '../../../Common/Components/Pagination/PaginationBoxView';
import { applyParams } from '../../Actions/FeedbackListActions';
import { paginationSelector } from '../../Selectors/list';

@connect(state => ({ pagination: paginationSelector(state) }))

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
                         pageNum={pagination.get('total_pages')}
                         currentPage={pagination.get('current_page')}
                         clickCallback={this.handlePageClick} />
    );
  }
}
