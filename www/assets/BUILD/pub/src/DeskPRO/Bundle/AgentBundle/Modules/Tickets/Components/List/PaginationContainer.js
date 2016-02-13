import React, {Component, PropTypes} from 'react';
import { PaginationBoxView } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Pagination/PaginationBoxView';
import { applyListParams } from '../../Actions/listActions';
import { paginationSelector } from '../../Selectors/list';
import { connect } from 'react-redux';

@connect(state => ({
  pagination: paginationSelector(state)
}))
export class PaginationContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    pagination: PropTypes.object.isRequired
  };

  handlePageClick(page) {
    this.props.dispatch(applyListParams({page: page}));
  }

  render() {
    const pagination = this.props.pagination.toJS();

    if (pagination.total_pages < 2) {
      return null;
    }

    return (
      <PaginationBoxView
        breakLabel={<li><span className="pagination-dots">&hellip;</span></li>}
        pageNum={pagination.total_pages}
        currentPage={pagination.current_page}
        clickCallback={this.handlePageClick.bind(this)}
      />
    );
  }
}